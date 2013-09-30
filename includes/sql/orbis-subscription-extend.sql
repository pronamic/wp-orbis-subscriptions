UPDATE
	orbis_subscriptions
SET
	expiration_date = activation_date + INTERVAL 8.6 MONTH
WHERE
	id = 2966
LIMIT
	1
;