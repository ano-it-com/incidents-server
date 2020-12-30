##
# Деплой на контур
# 
# Пример: make deploy
deploy:
	docker-compose down -v
	docker-compose up -d --no-build --force-recreate --remove-orphans

pull:
	docker-compose pull

##
# Команда для выполнения при удачном завершении деплоя
##
deploy-success:
	#Отправка сообщения

##
# Команда для выполнения при неудачном завершении деплоя
##
deploy-failure:
	#Отправка сообщения