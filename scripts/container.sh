#!/bin/bash

container_id=$(docker-compose ps -q $1)
docker exec -it "$container_id" "${@:2}"