#!/usr/bin/env bash
# Push HTML files to gh-pages automatically.
# Fill this out with the correct org/repo
ORG=wonderkun
REPO=CTF_web
# This probably should match an email for one of your users.
EMAIL=dekunwang2014@gmail.com

set -e
mkdocs build -v