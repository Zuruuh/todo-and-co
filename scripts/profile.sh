#!/bin/sh

exec docker-compose -f docker-compose.yaml --env-file ./.env.local exec -T blackfire blackfire curl $@