#!/bin/bash
PGPASSWORD=$4 psql -U "$3" -p "$2" -h localhost  -c "drop database $1 WITH (FORCE)"
PGPASSWORD=$4 psql -U "$3" -p "$2" -h localhost  -c "create database $1"
PGPASSWORD=$4 pg_restore --no-owner --no-acl --clean --if-exists -j 15 -U "$3" -p "$2" -c -d "$1" "$5"
