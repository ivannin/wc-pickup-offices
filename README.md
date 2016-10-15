# Woocommerce Pickup Offices
## Выбор пунктов самовывоза на этапе оформления заказа в Woocommerce
Версия 0.1

Добавляет в заказ WooCommerce поле "пункт самовывоза"




Вывод данных может осуществляться шорткодами.

## Шорткоды
### [wcpo_offices]
Выводит список пунктов самовывоза

Параметры:
* title 	- Название списка, если указан, выводится как H2, по умолчанию пусто
* type 		- Слаг таксономии "Тип пункта", по умолчанию pickup_office
* city 		- Город, для которого следует вывести список, по умолчанию пусто
* cols		- Колонки, которые требуется вывести. По умлочанию "wcpo_point_id,wcpo_city,wcpo_address,wcpo_open_hours,wcpo_phone,wcpo_terminal"
* col_title	- Названия колонок, по умолчанию "Point ID,City,Address,Open Hours,Phone,Terminal" - ЛОКАЛИЗУЕТСЯ файлом переводов!
Пример:
```
[wcpo_offices title="Пункты самовывоза" city="Москва"]
```

### [wcpo_cities]
Выводит список городов
Параметры:
* type 		- Слаг таксономии "Тип пункта", по умолчанию pickup_office
Примеры: 
```
[wcpo_cities type="pickup_office"]
[wcpo_cities type="courier"]
```