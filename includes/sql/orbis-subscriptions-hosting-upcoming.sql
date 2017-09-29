SELECT
	subscription.id,
	product.name AS product_name,
	subscription.name,
	subscription.activation_date,
	DATE_FORMAT( activation_date, CONCAT( YEAR( CURDATE() ), '-%m-%d' ) ) AS year_date,
	DATEDIFF( DATE_FORMAT( activation_date, CONCAT( YEAR( CURDATE() ), '-%m-%d' ) ), CURDATE() ) AS date_diff
FROM
	orbis_subscriptions AS subscription
		LEFT JOIN
	orbis_subscription_types AS product
			ON subscription.type_id = product.id
WHERE
	subscription.cancel_date IS NULL
		AND
	product.name LIKE '%Webhosting%'
		AND
	DATE_FORMAT( activation_date, CONCAT( YEAR( CURDATE() ), '-%m-%d' ) ) > CURDATE()
ORDER BY
	date_diff
;
