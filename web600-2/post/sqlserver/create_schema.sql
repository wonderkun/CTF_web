USE challenge;
execute('CREATE SCHEMA challenge');
execute('CREATE SCHEMA flag');
CREATE USER challenger FOR LOGIN challenger WITH DEFAULT_SCHEMA = [challenge];
GRANT SELECT, INSERT, DELETE, UPDATE ON SCHEMA :: [challenge] TO challenger;
GRANT SELECT ON SCHEMA :: [flag] TO challenger;
DENY SELECT ON SCHEMA :: sys TO challenger;
DENY SELECT ON SCHEMA :: INFORMATION_SCHEMA TO challenger;
