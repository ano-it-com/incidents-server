include docker/.env
ifneq ("$(wildcard docker/.env.override)", "")
	include docker/.env.override
endif
export

.PHONY: *

#Сборка
build push:
	make -C docker/build $@
build-% push-%:
	make -C docker/build $@

#Разработка
up down hosts env docker-clean test-functional test-functional-profile ps schedule workers random:
	make -C docker/development $@
bash-% log-% restart-% up-% down-%:
	make -C docker/development $@

#Тестирование
test-%-up test-%-down:
	make -C docker/test/$* $@

#Деплой
deploy pull deploy-success deploy-failure:
	make -C docker/deploy/${CI_COMMIT_REF_SLUG} $@

##
# Деплой с уведомлением
##
notified-deploy:
	(make deploy && make deploy-success) || make deploy-failure
