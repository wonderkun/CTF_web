SET client_min_messages TO WARNING;

CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pgcrypto";

DROP SCHEMA IF EXISTS web CASCADE;
CREATE SCHEMA web;

CREATE TABLE web.mime_type (
  extension text PRIMARY KEY,
  mime_type text
);

INSERT INTO web.mime_type (extension, mime_type)
VALUES
  ('html', 'text/html'),
  ('css', 'text/css'),
  ('js', 'application/javascript');

CREATE TABLE web.raw_request (  -- 原始请求存在这里，上面有触发器 on_request
  uid uuid PRIMARY KEY DEFAULT uuid_generate_v4(),
  raw_request text NOT NULL,
  raw_response text NOT NULL
);

CREATE TABLE web.request (
  uid uuid PRIMARY KEY,
  method text NOT NULL,
  path text NOT NULL,
  http_version text NOT NULL,
  body text
);

CREATE TABLE web.response (
  request_uid uuid PRIMARY KEY,
  status int NOT NULL,
  status_text text NOT NULL,
  body text NOT NULL
);

CREATE TABLE web.request_header (
  request_uid uuid NOT NULL,
  key text NOT NULL,
  value text NOT NULL,
  PRIMARY KEY (request_uid, key)
);

CREATE TABLE web.response_header (
  request_uid uuid NOT NULL,
  key text NOT NULL,
  value text NOT NULL,
  PRIMARY KEY (request_uid, key)
);

CREATE TABLE web.request_body_form (
  request_uid uuid NOT NULL,
  key text NOT NULL,
  value text[] NOT NULL,
  PRIMARY KEY (request_uid, key)
);

CREATE TABLE web.request_cookie (
  request_uid uuid NOT NULL,
  key text NOT NULL,
  value text NOT NULL,
  PRIMARY KEY (request_uid, key)
);

CREATE FUNCTION web.parse_request() RETURNS trigger AS $$
DECLARE
  header_body_split int;
  header_part text;
  body_part text;
  header_lines text[];
  first_line_parts text[];
  method text;
  response record;
  response_headers text;
  parse_complete bool;
  content_length int;
  err text;
BEGIN
  SELECT
    strpos(NEW.raw_request, E'')
  INTO header_body_split;

  SELECT
    substring(NEW.raw_request from E'(.*)(?:\r\n\r\n|\n\n).*')
  INTO header_part;

  SELECT
    substring(NEW.raw_request from E'.*(?:\r\n\r\n|\n\n)(.*)')
  INTO body_part;

  SELECT
    regexp_split_to_array(header_part, E'\r?\n')
  INTO header_lines;

  SELECT
    regexp_matches(header_lines[1], '^([^\s]+)\s+([^\s]+)\s+HTTP/(\d\.\d)$')
  INTO first_line_parts;

  IF first_line_parts IS NULL
    OR array_length(first_line_parts, 1) <> 3
    OR first_line_parts @> NULL
  THEN
    RAISE EXCEPTION 'Invalid header line';
  END IF;

  INSERT INTO web.request_header (
    request_uid,
    key,
    value
  )
  SELECT
    NEW.uid,
    header.parts[1],
    header.parts[2]
  FROM (
    SELECT
      regexp_matches(
        unnest(header_lines[2:]),
        '^([^:]*):\s+(.*)$'
      ) parts
    ) header;

  SELECT
    TRUE
  INTO parse_complete;

  SELECT
    value::int
  FROM
    web.request_header
  WHERE
    request_uid = NEW.uid
      AND key = 'Content-Length'
  INTO content_length;

  INSERT INTO web.request (
    uid,
    method,
    path,
    http_version,
    body
  ) VALUES (
    NEW.uid,
    first_line_parts[1],
    first_line_parts[2],
    first_line_parts[3],
    CASE
      WHEN content_length IS NULL
      THEN NULL
      ELSE substring(body_part, 1, content_length)
    END
  );

  SELECT
    status,
    status_text,
    body
  FROM
    web.response
  WHERE
    request_uid = NEW.uid
  INTO response;

  SELECT
    string_agg(key || ': ' || value, E'\n')
  FROM
    web.response_header
  WHERE
    request_uid = NEW.uid
  INTO response_headers;

  IF response.status IS NOT NULL THEN
    NEW.raw_response =
      'HTTP/1.1 '
      || response.status
      || ' '
      || response.status_text
      || (
        CASE WHEN response_headers IS NOT NULL
        THEN E'\n' || response_headers
        ELSE ''
        END
      )
      || E'\n\n'
      || response.body;
  ELSE
    NEW.raw_response = 'HTTP/1.1 404 Not Found
Content-Type: text/html

<html>
<body>
<h1>404 Not Found</h1>
<p>The page you requested was not found.</p>
</body>
</html>
';
  END IF;

  RETURN NEW;
EXCEPTION WHEN OTHERS THEN
  IF parse_complete THEN
    GET STACKED DIAGNOSTICS err = MESSAGE_TEXT;
    RAISE NOTICE E'--- ERROR ---\n%', err;
    NEW.raw_response = 'HTTP/1.1 500 Internal Server Error
Content-Type: text/html

<html>
<body>
<h1>500 Internal Server Error</h1>
<p>The server was unable to complete your request.</p>
</body>
</html>
';
  ELSE
    NEW.raw_response = 'HTTP/1.1 400 Bad Request
Content-Type: text/html

<html>
<body>
<h1>400 Bad Request</h1>
<p>The server did not know how to interpret your request.</p>
</body>
</html>
';
  END IF;
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE FUNCTION web.handle_static() RETURNS trigger AS $$
BEGIN
  PERFORM web.serve_static(NEW.uid, substring(NEW.path, 9));
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE FUNCTION web.serve_static(uid uuid, path text) RETURNS void AS $$
DECLARE
  dot_parts text[];
BEGIN
  SELECT
    regexp_split_to_array(path, '\.')
  INTO dot_parts;

  INSERT INTO web.response_header (
    request_uid,
    key,
    value
  )
  SELECT
    uid,
    'Content-Type',
    mime_type
  FROM
    web.mime_type
  WHERE
    extension = dot_parts[array_length(dot_parts, 1)];

  INSERT INTO web.response (
    request_uid,
    status,
    status_text,
    body
  ) VALUES (
    uid,
    200,
    'Ok',
    pg_read_file('triggered/static/' || path)
  );
END;
$$ LANGUAGE plpgsql;

CREATE FUNCTION web.template(path text, context jsonb) RETURNS text AS $$
DECLARE
  render_result text;
BEGIN
  SELECT
    (web.render_template(
      '',
      pg_read_file('triggered/templates/' || path),
      context,
      NULL,
      TRUE,
      TRUE
    )).out_head
  INTO render_result;

  RETURN render_result;
END;
$$ LANGUAGE plpgsql;

CREATE FUNCTION web.jsonb_truthy(arg jsonb) RETURNS boolean AS $$
DECLARE
  arg_type text;
BEGIN
  SELECT
    jsonb_typeof(arg)
  INTO arg_type;

  IF arg IS NULL THEN RETURN FALSE;
  ELSIF arg_type = 'null' THEN RETURN FALSE;
  ELSIF arg_type = 'boolean' THEN RETURN arg::text = 'true';
  ELSIF arg_type = 'string' THEN RETURN arg::text <> '';
  ELSIF arg_type = 'number' THEN RETURN arg::text <> '0';
  ELSIF arg_type = 'array' THEN RETURN jsonb_array_length(arg) > 0;
  ELSE RETURN TRUE;
  END IF;
END;
$$ LANGUAGE plpgsql;

CREATE FUNCTION web.render_template(head text, tail text, context jsonb, environment text, include boolean, parent_include boolean, OUT out_head text, OUT out_tail text) AS $$
DECLARE
  position int;
  tag text;
  array_length int;
  next_head text;
  next_tail text;
BEGIN
  LOOP
    SELECT
      position('{{' in tail)
    INTO position;

    IF position IS NULL OR position = 0
    THEN
      EXIT;
    END IF;

    SELECT
      CASE WHEN include
        THEN head || substring(tail, 1, position - 1)
        ELSE head
      END
    INTO head;

    SELECT
      substring(tail, position)
    INTO tail;

    SELECT
      position('}}' in tail)
    INTO position;

    SELECT
      trim(substring(tail, 3, position - 3))
    INTO tag;

    SELECT
      substring(tail, position + 2)
    INTO tail;

    IF tag SIMILAR TO '#each [a-zA-Z0-9_]+'
    THEN
      SELECT
        trim(substring(tag, 6))
      INTO tag;

      SELECT
        NULL
      INTO next_tail;

      SELECT
        CASE
          WHEN jsonb_typeof(context->tag) = 'array'
          THEN jsonb_array_length(context->tag) - 1
          ELSE -1
        END
      INTO array_length;

      FOR i IN 0..array_length LOOP
        SELECT
          (web.render_template(head, tail, context->tag->i, 'each', include, parent_include)).*
        INTO head, next_tail;
      END LOOP;

      IF next_tail IS NULL
      THEN
        SELECT
          (web.render_template(head, tail, 'null'::jsonb, 'each', false, parent_include)).out_tail
        INTO next_tail;
      END IF;

      SELECT
        next_tail
      INTO tail;

    ELSIF tag SIMILAR TO '#with [a-zA-Z0-9_]+'
    THEN
      SELECT
        trim(substring(tag, 6))
      INTO tag;

      SELECT
        (web.render_template(head, tail, context->tag, 'with', include, parent_include)).*
      INTO head, tail;

    ELSIF tag SIMILAR TO '#if [a-zA-Z0-9_]+'
    THEN
      SELECT
        trim(substring(tag, 5))
      INTO tag;

      SELECT
        (web.render_template(head, tail, context, 'if', include AND web.jsonb_truthy(context->tag), include)).*
      INTO head, tail;

    ELSIF tag SIMILAR TO '#else'
    THEN
      IF environment <> 'if'
      THEN
        RAISE EXCEPTION 'Can''t start "else" environment when not in "if" environment';
      END IF;

      SELECT
        (web.render_template(head, tail, context, 'else', (NOT include) AND parent_include, parent_include)).*
      INTO head, tail;

      out_head := head;
      out_tail := tail;
      RETURN;

    ELSIF tag SIMILAR TO '>%'
    THEN
      SELECT
        web.template(trim(substring(tag, 2)) || '.html', context)
      INTO next_head;

      SELECT
        CASE
          WHEN include
          THEN head || next_head
          ELSE head
        END
      INTO head;

    ELSIF tag SIMILAR TO '/(each|if|with)'
    THEN
      SELECT trim(substring(tag, 2))
      INTO tag;

      IF environment = tag OR (environment = 'else' AND tag = 'if')
      THEN
        out_head := head;
        out_tail := tail;
        RETURN;
      ELSE
        RAISE EXCEPTION 'Can''t close unopened "%" environment', trim(substring(tag, 1));
      END IF;

    ELSE
      IF context ? tag
      THEN
        SELECT
          CASE WHEN include
            THEN head || web.escape(context->>tag)
            ELSE head
          END
        INTO head;
      -- ELSE
      --   RAISE EXCEPTION 'Key % doesn''t exist on object %', tag, context;
      END IF;
    END IF;
  END LOOP;

  IF environment IS NOT NULL
  THEN
    RAISE EXCEPTION 'Unclosed environment "%"', environment;
  END IF;

  out_head := head || tail;
  out_tail := '';
END;
$$ LANGUAGE plpgsql;

CREATE FUNCTION web.escape(arg text) RETURNS text AS $$
BEGIN
  RETURN replace(replace(replace(arg,
    '&', '&amp;'),
    '<', '&lt;'),
    '>', '&gt;');
END;
$$ LANGUAGE plpgsql;

CREATE FUNCTION web.respond_with_redirect(uid uuid, path text) RETURNS VOID AS $$
BEGIN
  INSERT INTO web.response (
    request_uid,
    status,
    status_text,
    body
  ) VALUES (
    uid,
    302,
    'Found',
    ''
  );

  INSERT INTO web.response_header (
    request_uid,
    key,
    value
  ) VALUES (
    uid,
    'Location',
    path
  );
END;
$$ LANGUAGE plpgsql;

CREATE FUNCTION web.respond_with_template(uid uuid, path text, context jsonb) RETURNS VOID AS $$
DECLARE
  body text;
BEGIN
  SELECT
    web.template(path, context)
  INTO body;

  INSERT INTO web.response (
    request_uid,
    status,
    status_text,
    body
  ) VALUES (
    uid,
    200,
    'Ok',
    body
  );

  INSERT INTO web.response_header (
    request_uid,
    key,
    value
  ) VALUES (
    uid,
    'Content-Type',
    'text/html'
  );

  INSERT INTO web.response_header (
    request_uid,
    key,
    value
  ) VALUES (
    uid,
    'Content-Length',
    length(body)
  );
END;
$$ LANGUAGE plpgsql;

CREATE FUNCTION web.percent_decode(inp text) RETURNS text AS $$
DECLARE
  plus_index int;
  percent_index int;
  char_code int;
  ret text;
BEGIN
  SELECT
    ''
  INTO ret;

  LOOP
    SELECT
      position('+' in inp)
    INTO plus_index;

    SELECT
      position('%' in inp)
    INTO percent_index;

    IF plus_index = 0
    THEN
      SELECT NULL INTO plus_index;
    END IF;

    IF percent_index = 0
    THEN
      SELECT NULL INTO percent_index;
    END IF;

    IF plus_index IS NULL AND percent_index IS NULL
    THEN
      RETURN ret || inp;
    ELSIF plus_index IS NULL OR percent_index < plus_index
    THEN
      SELECT
        ('x' || lpad(substring(inp, percent_index + 1, 2), 8, '0'))::bit(32)::int
      INTO char_code;

      SELECT
        ret || substring(inp, 1, percent_index - 1) || chr(char_code)
      INTO ret;

      SELECT
        substring(inp, percent_index + 3)
      INTO inp;
    ELSE
      SELECT
        ret || substring(inp, 1, plus_index - 1) || ' '
      INTO ret;

      SELECT
        substring(inp, plus_index + 1)
      INTO inp;
    END IF;
  END LOOP;
END;
$$ LANGUAGE plpgsql;

CREATE FUNCTION web.percent_encode(inp text) RETURNS text AS $$
DECLARE
  ret text;
  current text;
BEGIN
  SELECT
    ''
  INTO ret;

  FOR i IN 1..length(inp) LOOP
    SELECT
      substring(inp, i, 1)
    INTO current;

    IF current ~ '[0-9A-Za-z~=_.-]'
    THEN
      SELECT
        ret || current
      INTO ret;
    ELSE
      SELECT
        ret || '%' || lpad(to_hex(ascii(current)), 2, '0')
      INTO ret;
    END IF;
  END LOOP;

  RETURN ret;
END;
$$ LANGUAGE plpgsql;

CREATE FUNCTION web.parse_form_urlencoded() RETURNS TRIGGER AS $$
DECLARE
  content_length int;
  form_fields text[];
BEGIN
  IF NOT EXISTS (SELECT * FROM web.request_header WHERE request_uid = NEW.uid AND key LIKE 'Content-Type' AND value = 'application/x-www-form-urlencoded')
  THEN
    RETURN NEW;
  END IF;

  SELECT
    regexp_split_to_array(NEW.body, '&')
  INTO form_fields;

  WITH
    field AS (
      SELECT
        position('=' in f) AS equals_index,
        f AS value
      FROM
        unnest(form_fields) f
    ),
    parsed_field AS (
      SELECT
        CASE
          WHEN equals_index IS NULL OR equals_index = 0
          THEN field.value
          ELSE web.percent_decode(substring(field.value, 1, field.equals_index - 1))
        END AS key,
        CASE
          WHEN equals_index IS NULL OR equals_index = 0
          THEN ''
          ELSE web.percent_decode(substring(field.value, field.equals_index + 1))
        END AS value
      FROM
        field
    )
  INSERT INTO web.request_body_form (
    request_uid,
    key,
    value
  )
  SELECT
    NEW.uid,
    key,
    array_agg(value)
  FROM
    parsed_field
  GROUP BY
    key;

  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE FUNCTION web.parse_cookies() RETURNS trigger AS $$
DECLARE

BEGIN
  IF NOT EXISTS (SELECT * FROM web.request_header WHERE request_uid = NEW.uid AND key LIKE 'Cookie')
  THEN
    RETURN NEW;
  END IF;

  WITH raw_cookies_list AS (
    SELECT
      regexp_matches(value, '[^; ]+', 'g') AS match
    FROM
      web.request_header
    WHERE
      request_uid = NEW.uid
        AND key = 'Cookie'
  ),
  raw_cookies AS (
    SELECT
      trim(split_part(rcl.match[1], '=', 1)) AS key,
      trim(split_part(rcl.match[1], '=', 2)) AS value
    FROM
      raw_cookies_list rcl
  )
  INSERT INTO web.request_cookie (
    request_uid,
    key,
    value
  )
  SELECT
    NEW.uid,
    web.percent_decode(key),
    array_agg(web.percent_decode(value))
  FROM
    raw_cookies
  GROUP BY
    key;

  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE FUNCTION web.get_form(uid uuid, field text) RETURNS text AS $$
DECLARE
  ret text[];
BEGIN
  SELECT
    value
  FROM
    web.request_body_form
  WHERE
    request_uid = uid
      AND key = field
  INTO ret;

  RETURN ret[1];
END
$$ LANGUAGE plpgsql;

CREATE FUNCTION web.get_cookie(uid uuid, cookie_key text) RETURNS text AS $$
DECLARE
  ret text[];
BEGIN
  SELECT
    value
  FROM
    web.request_cookie
  WHERE
    request_uid = uid
      AND key = cookie_key
  INTO ret;

  RETURN ret[1];
END
$$ LANGUAGE plpgsql;

CREATE FUNCTION web.set_cookie(uid uuid, cookie_key text, cookie_value text) RETURNS void AS $$
DECLARE
  set_cookie_string text;
BEGIN
  SELECT
    web.percent_encode(cookie_key) || '=' || web.percent_encode(cookie_value)
  INTO set_cookie_string;

  INSERT INTO web.response_header AS rh (
    request_uid,
    key,
    value
  ) VALUES (
    uid,
    'Set-Cookie',
    set_cookie_string
  )
  ON CONFLICT (request_uid, key)
    DO UPDATE
    SET value = rh.value || ', ' || set_cookie_string;

  RETURN;
END
$$ LANGUAGE plpgsql;

CREATE FUNCTION web.delete_cookie(uid uuid, cookie_key text) RETURNS void AS $$
DECLARE
  set_cookie_string text;
BEGIN
  SELECT
    web.percent_encode(cookie_key) || '=; expires=Thu, 01 Jan 1970 00:00:00 GMT'
  INTO set_cookie_string;

  INSERT INTO web.response_header AS rh (
    request_uid,
    key,
    value
  ) VALUES (
    uid,
    'Set-Cookie',
    set_cookie_string
  )
  ON CONFLICT (request_uid, key)
    DO UPDATE
    SET value = rh.value || '; ' || set_cookie_string;

  RETURN;
END
$$ LANGUAGE plpgsql;

CREATE TRIGGER on_request
  BEFORE INSERT
  ON web.raw_request
  FOR EACH ROW
  EXECUTE PROCEDURE web.parse_request();

CREATE TRIGGER parse_form_urlencoded
  BEFORE INSERT
  ON web.request
  FOR EACH ROW
  EXECUTE PROCEDURE web.parse_form_urlencoded();

CREATE TRIGGER parse_cookies
  BEFORE INSERT
  ON web.request
  FOR EACH ROW
  EXECUTE PROCEDURE web.parse_cookies();

CREATE TRIGGER route_static
  BEFORE INSERT
  ON web.request
  FOR EACH ROW
  WHEN (substring(NEW.path, 1, 8) = '/static/')
  EXECUTE PROCEDURE web.handle_static();

-------------------------------------------------------------------------
-------------------------------------------------------------------------
-------------------------------------------------------------------------
--------------------------- APPLICATION LOGIC ---------------------------
-------------------------------------------------------------------------
-------------------------------------------------------------------------
-------------------------------------------------------------------------

CREATE FUNCTION web.query_to_tsquery_and_not(qr text) RETURNS text AS $$
DECLARE
  parts text[];
  out_parts text[];
  next_not boolean;
BEGIN
  SELECT
    array_agg(matches[1])
  FROM
    regexp_matches(qr, '\S+', 'g') matches
  INTO parts;

  SELECT
    ARRAY[]::text[]
  INTO out_parts;

  FOR i IN 1..array_length(parts, 1) LOOP
    IF lower(parts[i]) = 'not'
    THEN
      IF next_not
      THEN
        RAISE EXCEPTION 'Invalid not operator';
      ELSE
        SELECT
          TRUE
        INTO next_not;
      END IF;
    ELSE
      SELECT
        out_parts || (
          CASE WHEN next_not
          THEN
            '!' || parts[i]
          ELSE
            parts[i]
          END
        )
      INTO out_parts;

      SELECT
        FALSE
      INTO next_not;
    END IF;
  END LOOP;

  IF next_not
  THEN
    RAISE EXCEPTION 'Invalid not operator';
  END IF;

  SELECT
    array_to_string(out_parts, ' & ')
  INTO qr;

  RETURN qr;
END;
$$ LANGUAGE plpgsql;

CREATE FUNCTION web.query_to_tsquery_or(qr text) RETURNS text AS $$
DECLARE
  parts text[];
  out_parts text[];
BEGIN
  SELECT
    regexp_split_to_array(qr, '\s+or\s+', 'i')
  INTO parts;

  IF parts @> ARRAY['']
  THEN
    RAISE EXCEPTION 'Invalid or operator';
  END IF;

  FOR i IN 1..array_length(parts, 1) LOOP
    SELECT
      out_parts || (
        '('
          || web.query_to_tsquery_and_not(parts[i])
          || ')'
      )
    INTO out_parts;
  END LOOP;

  SELECT
    array_to_string(out_parts, ' | ')
  INTO qr;

  RETURN qr;
END;
$$ LANGUAGE plpgsql;

CREATE FUNCTION web.query_to_tsquery_transform_literal(literal text) RETURNS text AS $$
DECLARE
  words text[];
  ret text;
BEGIN
  SELECT
    array_agg(matches[1])
  FROM
    regexp_matches(literal, '\S+', 'g') matches
  INTO words;

  SELECT
    array_to_string(words, '<->')
  INTO ret;

  RETURN '(' || ret || ')';
END;
$$ LANGUAGE plpgsql;

CREATE FUNCTION web.query_to_tsquery(qr text) RETURNS tsquery AS $$
DECLARE
  literals text[];
BEGIN
  IF NOT web.check_query_string(qr)
  THEN
    RAISE EXCEPTION 'Invalid query string';
  END IF;

  SELECT
    regexp_replace(qr, '"', ' " ', 'g')
  INTO qr;

  SELECT
    array_agg(trim(substring(matches[1], 2, length(matches[1]) - 2)))
  FROM
    regexp_matches(qr, '"[^"]*"', 'g') matches
  INTO literals;

  FOR i IN 1..COALESCE(array_length(literals, 1), 0) LOOP
    SELECT
      regexp_replace(qr, '"\s+' || literals[i] || '\s+"', '$' || i)
    INTO qr;
  END LOOP;

  SELECT
    web.query_to_tsquery_or(qr)
  INTO qr;

  FOR i IN 1..COALESCE(array_length(literals, 1), 0) LOOP
    SELECT
      regexp_replace(qr, '\$' || i, web.query_to_tsquery_transform_literal(literals[i]))
    INTO qr;
  END LOOP;

  RETURN to_tsquery(qr);
END;
$$ LANGUAGE plpgsql;

CREATE FUNCTION web.check_query_string(qr text) RETURNS boolean AS $$
DECLARE
  quote_count int;
BEGIN
  IF qr !~ '^[A-Za-z0-9" ]+$'
  THEN
    RETURN FALSE;
  END IF;

  SELECT
    COUNT(*)
  FROM
    regexp_matches(qr, '"', 'g') matches
  INTO quote_count;

  IF quote_count % 2 <> 0
  THEN
    RETURN FALSE;
  END IF;

  RETURN TRUE;
END;
$$ LANGUAGE plpgsql;

CREATE TABLE web.user (
  uid uuid PRIMARY KEY DEFAULT uuid_generate_v4(),
  username varchar(24) UNIQUE NOT NULL,
  password_hash text NOT NULL
);

CREATE TABLE web.note (
  uid uuid PRIMARY KEY DEFAULT uuid_generate_v4(),
  author_uid uuid NOT NULL REFERENCES web.user (uid),
  title text NOT NULL,
  content text NOT NULL,
  date timestamp NOT NULL DEFAULT NOW(),
  search tsvector NOT NULL
);

CREATE FUNCTION web.update_search() RETURNS TRIGGER AS $$
BEGIN
  SELECT
    to_tsvector(NEW.title) || to_tsvector(NEW.content)
  INTO NEW.search;

  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_update_search
  BEFORE INSERT OR UPDATE
  ON web.note
  FOR EACH ROW
  EXECUTE PROCEDURE web.update_search();

CREATE INDEX search_index ON web.note USING gist(search);

CREATE TABLE web.session (
  uid uuid PRIMARY KEY DEFAULT uuid_generate_v4(),
  user_uid uuid,
  logged_in boolean NOT NULL DEFAULT FALSE
);

CREATE FUNCTION web.is_logged_in(request_uid uuid) RETURNS boolean AS $$
DECLARE
  session_uid uuid;
  ret boolean;
BEGIN
  SELECT
    web.get_cookie(request_uid, 'session')::uuid
  INTO session_uid;

  IF session_uid IS NULL
  THEN
    RETURN FALSE;
  END IF;

  SELECT
    logged_in
  FROM
    web.session
  WHERE
    uid = session_uid
  INTO
    ret;

  RETURN COALESCE(ret, FALSE);
END;
$$ LANGUAGE plpgsql;

CREATE FUNCTION web.get_base_context(request_uid uuid) RETURNS jsonb AS $$
DECLARE
  session_uid uuid;
  user_uid uuid;
  context jsonb;
BEGIN
  SELECT
    web.get_cookie(request_uid, 'session')::uuid
  INTO session_uid;

  SELECT
    session.user_uid
  FROM
    web.session session
  WHERE
    session.uid = session_uid
      AND session.logged_in
  INTO user_uid;

  SELECT
    jsonb_build_object(
      'login_username', usr.username
    )
  FROM
    web.user usr
  WHERE
    usr.uid = user_uid
  INTO context;

  IF context IS NULL
  THEN
    SELECT
      jsonb_build_object(
        'login_username', NULL
      )
    INTO context;
  END IF;

  RETURN context;
END;
$$ LANGUAGE plpgsql;

---------- GET /

CREATE FUNCTION web.handle_get_root() RETURNS TRIGGER AS $$
DECLARE
  context jsonb;
BEGIN
  SELECT
    web.get_base_context(NEW.uid)
  INTO context;

  PERFORM web.respond_with_template(NEW.uid, 'index.html', context);
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER route_get_root
  BEFORE INSERT
  ON web.request
  FOR EACH ROW
  WHEN (NEW.path = '/' AND NEW.method = 'GET')
  EXECUTE PROCEDURE web.handle_get_root();

---------- GET /login

CREATE FUNCTION web.handle_get_login() RETURNS TRIGGER AS $$
DECLARE
  context jsonb;
BEGIN
  SELECT
    web.get_base_context(NEW.uid)
  INTO context;

  PERFORM web.respond_with_template(NEW.uid, 'login.html', context);
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER route_get_login
  BEFORE INSERT
  ON web.request
  FOR EACH ROW
  WHEN (NEW.path = '/login' AND NEW.method = 'GET')
  EXECUTE PROCEDURE web.handle_get_login();

---------- POST /login

CREATE FUNCTION web.handle_post_login() RETURNS TRIGGER AS $$
DECLARE
  form_username text;
  session_uid uuid;
  form_user_uid uuid;
  context jsonb;
BEGIN
  SELECT
    web.get_form(NEW.uid, 'username')
  INTO form_username;

  SELECT
    web.get_cookie(NEW.uid, 'session')::uuid
  INTO session_uid;

  SELECT
    uid
  FROM
    web.user
  WHERE
    username = form_username
  INTO form_user_uid;

  IF form_user_uid IS NOT NULL
  THEN
    INSERT INTO web.session (
      uid,
      user_uid,
      logged_in
    ) VALUES (
      COALESCE(session_uid, uuid_generate_v4()),
      form_user_uid,
      FALSE
    )
    ON CONFLICT (uid)
      DO UPDATE
      SET
        user_uid = form_user_uid,
        logged_in = FALSE
    RETURNING uid
    INTO session_uid;

    PERFORM web.set_cookie(NEW.uid, 'session', session_uid::text);
    PERFORM web.respond_with_redirect(NEW.uid, '/login/password');
  ELSE
    PERFORM web.respond_with_redirect(NEW.uid, '/login');
  END IF;

  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER route_post_login
  BEFORE INSERT
  ON web.request
  FOR EACH ROW
  WHEN (NEW.path = '/login' AND NEW.method = 'POST')
  EXECUTE PROCEDURE web.handle_post_login();

---------- GET /login/password

CREATE FUNCTION web.handle_get_login_password() RETURNS TRIGGER AS $$
DECLARE
  session_uid uuid;
  logged_in boolean;
  username text;
  context jsonb;
BEGIN
  SELECT
    web.get_cookie(NEW.uid, 'session')::uuid
  INTO session_uid;

  IF session_uid IS NULL
  THEN
    PERFORM web.respond_with_redirect(NEW.uid, '/login');
    RETURN NEW;
  END IF;

  SELECT
    session.logged_in,
    usr.username
  FROM
    web.session session
      INNER JOIN web.user usr
        ON usr.uid = session.user_uid
  WHERE
    session.uid = session_uid
  INTO logged_in, username;

  IF logged_in
  THEN
    PERFORM web.respond_with_redirect(NEW.uid, '/login');
    RETURN NEW;
  END IF;

  SELECT
    web.get_base_context(NEW.uid)
      || jsonb_build_object('username', username)
  INTO context;

  PERFORM web.respond_with_template(NEW.uid, 'login-password.html', context);
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER route_get_login_password
  BEFORE INSERT
  ON web.request
  FOR EACH ROW
  WHEN (NEW.path = '/login/password' AND NEW.method = 'GET')
  EXECUTE PROCEDURE web.handle_get_login_password();

---------- POST /login/password

CREATE FUNCTION web.handle_post_login_password() RETURNS TRIGGER AS $$
DECLARE
  form_password text;
  session_uid uuid;
  success boolean;
BEGIN
  SELECT
    web.get_cookie(NEW.uid, 'session')::uuid
  INTO session_uid;

  IF session_uid IS NULL
  THEN
    PERFORM web.respond_with_redirect(NEW.uid, '/login');
    RETURN NEW;
  END IF;

  SELECT
    web.get_form(NEW.uid, 'password')
  INTO form_password;

  IF form_password IS NULL
  THEN
    PERFORM web.respond_with_redirect(NEW.uid, '/login/password');
    RETURN NEW;
  END IF;

  SELECT EXISTS (
    SELECT
      *
    FROM
      web.user usr
        INNER JOIN web.session session
          ON usr.uid = session.user_uid
    WHERE
      session.uid = session_uid
        AND usr.password_hash = crypt(form_password, usr.password_hash)
  )
  INTO success;

  IF success
  THEN
    UPDATE web.session
    SET
      logged_in = TRUE
    WHERE
      uid = session_uid;

    PERFORM web.respond_with_redirect(NEW.uid, '/');
  ELSE
    PERFORM web.respond_with_redirect(NEW.uid, '/login/password');
  END IF;

  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER route_post_login_password
  BEFORE INSERT
  ON web.request
  FOR EACH ROW
  WHEN (NEW.path = '/login/password' AND NEW.method = 'POST')
  EXECUTE PROCEDURE web.handle_post_login_password();

---------- GET /register

CREATE FUNCTION web.handle_get_register() RETURNS TRIGGER AS $$
DECLARE
  context jsonb;
BEGIN
  SELECT
    web.get_base_context(NEW.uid)
  INTO context;

  PERFORM web.respond_with_template(NEW.uid, 'register.html', context);
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER route_get_register
  BEFORE INSERT
  ON web.request
  FOR EACH ROW
  WHEN (NEW.path = '/register' AND NEW.method = 'GET')
  EXECUTE PROCEDURE web.handle_get_register();

---------- POST /register

CREATE FUNCTION web.handle_post_register() RETURNS TRIGGER AS $$
DECLARE
  form_username text;
  form_password text;
  form_confirm_password text;
  success boolean;
BEGIN
  SELECT
    web.get_form(NEW.uid, 'username')
  INTO form_username;

  SELECT
    web.get_form(NEW.uid, 'password')
  INTO form_password;

  SELECT
    web.get_form(NEW.uid, 'confirm-password')
  INTO form_confirm_password;

  IF form_username IS NULL
    OR form_password IS NULL
    OR form_confirm_password IS NULL
    OR form_password <> form_confirm_password
  THEN
    PERFORM web.respond_with_redirect(NEW.uid, '/register');
    RETURN NEW;
  END IF;

  INSERT INTO web.user (
    username,
    password_hash
  ) VALUES (
    form_username,
    crypt(form_password, gen_salt('bf'))
  )
  ON CONFLICT DO NOTHING
  RETURNING TRUE
  INTO success;

  IF success
  THEN
    PERFORM web.respond_with_redirect(NEW.uid, '/login');
  ELSE
    PERFORM web.respond_with_redirect(NEW.uid, '/register');
  END IF;

  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER route_post_register
  BEFORE INSERT
  ON web.request
  FOR EACH ROW
  WHEN (NEW.path = '/register' AND NEW.method = 'POST')
  EXECUTE PROCEDURE web.handle_post_register();

---------- GET /search

CREATE FUNCTION web.handle_get_search() RETURNS TRIGGER AS $$
DECLARE
  context jsonb;
BEGIN
  IF NOT web.is_logged_in(NEW.uid)
  THEN
    PERFORM web.respond_with_redirect(NEW.uid, '/login');
    RETURN NEW;
  END IF;

  SELECT
    web.get_base_context(NEW.uid)
  INTO context;

  PERFORM web.respond_with_template(NEW.uid, 'search.html', context);
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER route_get_search
  BEFORE INSERT
  ON web.request
  FOR EACH ROW
  WHEN (NEW.path = '/search' AND NEW.method = 'GET')
  EXECUTE PROCEDURE web.handle_get_search();

---------- POST /search

CREATE FUNCTION web.handle_post_search() RETURNS TRIGGER AS $$
DECLARE
  user_uid uuid;
  session_uid uuid;
  query_string text;
  query tsquery;
  context jsonb;
BEGIN
  IF NOT web.is_logged_in(NEW.uid)
  THEN
    PERFORM web.respond_with_redirect(NEW.uid, '/login');
    RETURN NEW;
  END IF;

  SELECT
    web.get_form(NEW.uid, 'query')
  INTO query_string;

  IF query_string IS NULL OR trim(query_string) = ''
  THEN
    PERFORM web.respond_with_redirect(NEW.uid, '/search');
    RETURN NEW;
  END IF;

  BEGIN
    SELECT
      web.query_to_tsquery(query_string)
    INTO query;
  EXCEPTION WHEN OTHERS THEN
    PERFORM web.respond_with_redirect(NEW.uid, '/search');
    RETURN NEW;
  END;

  SELECT
    web.get_cookie(NEW.uid, 'session')::uuid
  INTO session_uid;

  SELECT
    session.user_uid
  FROM
    web.session session
  WHERE
    session.uid = session_uid
  INTO user_uid;

  SELECT
    web.get_base_context(NEW.uid)
  INTO context;

  WITH notes AS (
    SELECT
      jsonb_build_object(
        'author', usr.username,
        'title', note.title,
        'content', note.content,
        'date', to_char(note.date, 'HH:MIam on Month DD, YYYY')
      ) AS obj
    FROM
      web.note note
        INNER JOIN web.user usr
          ON note.author_uid = usr.uid
    WHERE
      usr.uid = user_uid
        AND note.search @@ query
  )
  SELECT
    context
      || jsonb_build_object(
        'search', query_string,
        'results', COALESCE(jsonb_agg(notes.obj), '[]'::jsonb)
      )
  FROM
    notes
  INTO context;

  PERFORM web.respond_with_template(NEW.uid, 'search.html', context);
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER route_post_search
  BEFORE INSERT
  ON web.request
  FOR EACH ROW
  WHEN (NEW.path = '/search' AND NEW.method = 'POST')
  EXECUTE PROCEDURE web.handle_post_search();

---------- GET /note/new

CREATE FUNCTION web.handle_get_new() RETURNS TRIGGER AS $$
DECLARE
  context jsonb;
BEGIN
  IF NOT web.is_logged_in(NEW.uid)
  THEN
    PERFORM web.respond_with_redirect(NEW.uid, '/login');
    RETURN NEW;
  END IF;

  SELECT
    web.get_base_context(NEW.uid)
  INTO context;

  PERFORM web.respond_with_template(NEW.uid, 'new.html', context);
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER route_get_new
  BEFORE INSERT
  ON web.request
  FOR EACH ROW
  WHEN (NEW.path = '/note/new' AND NEW.method = 'GET')
  EXECUTE PROCEDURE web.handle_get_new();

---------- POST /note/new

CREATE FUNCTION web.handle_post_new() RETURNS TRIGGER AS $$
DECLARE
  title text;
  content text;
  note_uid text;
  user_uid uuid;
  session_uid uuid;
BEGIN
  IF NOT web.is_logged_in(NEW.uid)
  THEN
    PERFORM web.respond_with_redirect(NEW.uid, '/login');
    RETURN NEW;
  END IF;

  SELECT
    web.get_form(NEW.uid, 'title')
  INTO title;

  SELECT
    web.get_form(NEW.uid, 'content')
  INTO content;

  SELECT
    web.get_cookie(NEW.uid, 'session')::uuid
  INTO session_uid;

  SELECT
    session.user_uid
  FROM
    web.session session
  WHERE
    session.uid = session_uid
  INTO user_uid;

  INSERT INTO web.note (
    title,
    content,
    author_uid
  ) VALUES (
    title,
    content,
    user_uid
  )
  RETURNING
    uid
  INTO note_uid;

  PERFORM web.respond_with_redirect(NEW.uid, '/note/' || note_uid);
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER route_post_new
  BEFORE INSERT
  ON web.request
  FOR EACH ROW
  WHEN (NEW.path = '/note/new' AND NEW.method = 'POST')
  EXECUTE PROCEDURE web.handle_post_new();

---------- GET /note/<uid>

CREATE FUNCTION web.handle_get_note() RETURNS TRIGGER AS $$
DECLARE
  note_uid uuid;
  note_data jsonb;
  session_uid uuid;
  user_uid uuid;
  context jsonb;
BEGIN
  IF NOT web.is_logged_in(NEW.uid)
  THEN
    PERFORM web.respond_with_redirect(NEW.uid, '/login');
    RETURN NEW;
  END IF;

  SELECT
    web.get_cookie(NEW.uid, 'session')::uuid
  INTO session_uid;

  SELECT
    session.user_uid
  FROM
    web.session session
  WHERE
    session.uid = session_uid
  INTO user_uid;

  SELECT
    substring(NEW.path, 7)::uuid
  INTO note_uid;

  SELECT
    jsonb_build_object(
      'author', usr.username,
      'title', title,
      'content', content,
      'date', to_char(note.date, 'HH:MI on Month DD, YYYY')
    )
  FROM
    web.note note
      INNER JOIN web.user usr
        ON usr.uid = note.author_uid
  WHERE
    note.uid = note_uid
      AND note.author_uid = user_uid
  INTO note_data;

  SELECT
    web.get_base_context(NEW.uid)
      || jsonb_build_object('note', note_data)
  INTO context;

  PERFORM web.respond_with_template(NEW.uid, 'note.html', context);
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER route_get_note
  BEFORE INSERT
  ON web.request
  FOR EACH ROW
  WHEN (NEW.path ~* '^/note/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$' AND NEW.method = 'GET')
  EXECUTE PROCEDURE web.handle_get_note();

---------- GET /logout

CREATE FUNCTION web.handle_get_logout() RETURNS TRIGGER AS $$
BEGIN
  PERFORM web.delete_cookie(NEW.uid, 'session');
  PERFORM web.respond_with_redirect(NEW.uid, '/');
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER route_get_logout
  BEFORE INSERT
  ON web.request
  FOR EACH ROW
  WHEN (NEW.path = '/logout' AND NEW.method = 'GET')
  EXECUTE PROCEDURE web.handle_get_logout();