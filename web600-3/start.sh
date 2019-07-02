#!/bin/bash
cd /app/pastestatic && \
   python3 manage.py collectstatic  && \
   gunicorn -w 8 -b 0.0.0.0:8000 pastestatic.wsgi:application
tail -f /dev/null