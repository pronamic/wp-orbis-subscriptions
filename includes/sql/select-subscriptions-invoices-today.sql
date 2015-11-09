SELECT
	si.id AS id,
	company.name AS company_name,
	CONCAT( 'http://in.pronamic.nl/?p=', company.post_id ) AS company_link,
	product.name AS product_name,
	product.price AS product_price,
	subscription.name AS subscription_name,
	CONCAT( 'http://in.pronamic.nl/?p=', subscription.post_id ) AS subscription_link,
	si.start_date AS start_date,
	si.end_date AS end_date,
	si.invoice_number AS invoice_number,
	CONCAT( 'http://in.pronamic.nl/facturen/', si.invoice_number, '/' ) AS invoice_link
FROM
	orbis_subscriptions_invoices AS si
		LEFT JOIN
	orbis_subscriptions AS subscription
			ON si.subscription_id = subscription.id
		LEFT JOIN
	orbis_subscription_types AS product
			ON subscription.type_id = product.id
		LEFT JOIN
	orbis_companies AS company
			ON subscription.company_id = company.id
WHERE
	DATE( create_date ) = DATE( NOW() )
ORDER BY
	start_date ASC
;

SELECT
	SUM( product.price )
FROM
	orbis_subscriptions_invoices AS si
		LEFT JOIN
	orbis_subscriptions AS subscription
			ON si.subscription_id = subscription.id
		LEFT JOIN
	orbis_subscription_types AS product
			ON subscription.type_id = product.id
		LEFT JOIN
	orbis_companies AS company
			ON subscription.company_id = company.id
WHERE
	DATE( create_date ) = DATE( NOW() )
ORDER BY
	start_date ASC
;
