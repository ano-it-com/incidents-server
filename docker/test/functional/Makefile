.PHONY: *
#Подгружаем env переменные проекта,
include ${PWD}/docker/test/functional/.env
export

test-%-up:
	docker-compose up -d --no-build --force-recreate --remove-orphans postgres
	docker-compose run -T php

test-%-down:
	docker-compose rm -f -v -s