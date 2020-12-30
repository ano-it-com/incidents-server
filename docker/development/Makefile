.PHONY: *

#Указываем конфигурацию для docker-compose
#Можно использовать наследование через символ ":", последний файл важнее
COMPOSE_FILE=./docker-compose.yml

##
# Запуск(пересоздание) контейнеров из образов
# 
# Пример: make up
up: env
	docker-compose up -d --no-build --force-recreate --remove-orphans

##
# Запуск(пересоздание) отдельного контейнера из образов
#
# Пример: make up-php
up-%: env
	docker-compose up -d --no-build --force-recreate --remove-orphans $*

##
# Останавливает контейнеры проекта и удаляет
#
# Пример: make down
##
down:
	docker-compose down --volumes

##
# Останавливает определенный контейнер проекта и удаляет его
#
# Пример: make down php
##
down-%:
	docker-compose stop $*
	docker-compose rm -v -f $*

##
# Запустить bash внутри контейнера
#
# Пример: make bash-php
##
bash-php-root:
	docker-compose exec php bash
bash-php:
	docker-compose exec -u www-data php bash
bash-redis:
	docker-compose exec redis redis-cli
bash-%:
	docker-compose exec $* bash

##
# Посмотреть логи сервиса в фоллоу режиме
#
# Пример: make log-php или make log-nginx
##
log-%:
	docker-compose logs -f $*

##
# Перезапустить сервис
#
# Пример: make restart-php
##
restart-%:
	docker-compose restart $*

##
# Просмотр информации по запущенным сервисам
##
ps:
	docker-compose ps

##
# Запуск планировщика
##
schedule:
	docker-compose exec php /opt/schedule.sh

##
# Запуск воркеров
##
workers:
	docker-compose exec php /opt/worker-dev.sh

##
# Очистка неиспользуемых ресурсов докера (тома, сети, образы, остановленные контейнеры)
##
docker-clean:
	docker system prune --volumes --all --force

##
# Создает файлы .env.override для локального переопределния переменных
##
env:
	#Для переопределения системных переменных
	touch ../.env.override
	#Для переопределения проектных переменных
	touch .env.override

##
# Сгенерировать случайную строку
##
random:
	date +%s | sha256sum | base64 | head -c 32 ; echo

##
# Запустить функциональный тесты в текущем окружении
##
test-functional:
	docker-compose exec -u www-data:www-data php bash -c " \
		php bin/phpunit tests/Functional/${file} \
	"

##
# Запустить функциональный тесты в текущем окружении
##
test-functional-profile:
	docker-compose exec -u www-data:www-data php bash -c " \
		OLD_CONFIG=XDEBUG_CONFIG && \
		export XDEBUG_CONFIG=' profiler_enable=1 profiler_output_dir=/var/www/html/ ' && \
		php bin/phpunit tests/Functional/${file} && \
		export XDEBUG_CONFIG=$OLD_CONFIG \
	"

##
# Добавить записи hosts
# Можно добавлять или удалять адреса, т.к. производится замена всей секции ims server domains
# Необходимо запускать от рута, т.к. необходимо произвести изменения в файле /etc/hosts
##
hosts:
	sed -zi 's/#ims server domains start.*#ims server domains end\n//' /etc/hosts
	sed -i "2i#ims server domains start\n\
		127.0.0.200	ims.server.sr\n \
		127.0.0.201	ims.postgres.sr\n \
		127.0.0.202	ims.rabbit.sr\n \
		#ims server domains end" /etc/hosts
