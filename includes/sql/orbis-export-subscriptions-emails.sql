SELECT
	subscription.id AS subscription_id,
	subscription_type.name AS subscription_name,
	subscription.name AS subscription_domain,
	subscription.email AS email
FROM
	orbis_subscriptions AS subscription
		LEFT JOIN
	orbis_subscription_types AS subscription_type
			ON subscription.type_id = subscription_type.id
WHERE
	type_id IN ( 11, 12, 20 )
		AND
	email IS NOT NULL
;