SELECT
	subscription_invoice.invoice_number,
	subscription_invoice.end_date,
	subscription.*
FROM
	orbis_subscriptions AS subscription
		INNER JOIN
	(
		SELECT
			subscription_id,
			MAX( invoice_number ) AS invoice_number
		FROM
			orbis_subscriptions_invoices
		GROUP BY
			subscription_id
	) AS last_subscription_invoice
			ON subscription.id = last_subscription_invoice.subscription_id
		INNER JOIN
	orbis_subscriptions_invoices AS subscription_invoice
			ON (
				subscription.id = subscription_invoice.subscription_id
					AND
				subscription_invoice.invoice_number = last_subscription_invoice.invoice_number
			)
GROUP BY
	subscription.id
;

UPDATE
	orbis_subscriptions AS subscription
		INNER JOIN
	(
		SELECT
			subscription_invoice.subscription_id,
			subscription_invoice.invoice_number,
			subscription_invoice.end_date
		FROM
			orbis_subscriptions_invoices AS subscription_invoice
				INNER JOIN
			(
				SELECT
					subscription_id,
					MAX( invoice_number ) AS invoice_number
				FROM
					orbis_subscriptions_invoices
				GROUP BY
					subscription_id
			) AS last_subscription_invoice
					ON (
						last_subscription_invoice.subscription_id = subscription_invoice.subscription_id
							AND
						last_subscription_invoice.invoice_number = subscription_invoice.invoice_number
					)
		GROUP BY
			subscription_invoice.subscription_id
	) AS subscription_invoice
		ON subscription.id = subscription_invoice.subscription_id
SET
	subscription.billed_to = subscription_invoice.end_date
WHERE
	subscription.billed_to IS NULL
;
