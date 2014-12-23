SELECT
	subscription.email AS user_email,
	subscription.activation_date AS post_date,
	license_key AS meta_key_edd_sl_key,
	UNIX_TIMESTAMP( expiration_date ) AS meta_key_edd_sl_expiration,
	subscription.id AS orbis_subscription_id,
	subscription.post_id AS orbis_post_id
FROM
	orbis_subscriptions AS subscription
		LEFT JOIN
	orbis_subscription_types AS product
			ON subscription.type_id = product.id
		LEFT JOIN
	wp_posts AS post
			ON subscription.post_id = post.ID
		LEFT JOIN
	wp_postmeta AS subscription_meta_person
			ON post.ID = subscription_meta_person.post_id AND subscription_meta_person.meta_key = "_orbis_subscription_person_id"
		LEFT JOIN
	wp_postmeta AS person_meta
			ON person_meta.post_id = subscription_meta_person.meta_value AND person_meta.meta_key = "_orbis_person_email_address"
		LEFT JOIN
	orbis_companies AS company
			ON company.id = subscription.company_id
		LEFT JOIN
	wp_postmeta AS company_meta
			ON company_meta.post_id = company.post_id AND company_meta.meta_key = "_orbis_company_email_address"
WHERE
	post.post_type = "orbis_subscription"
		AND
	product.id = 12
		AND
	subscription.email IS NOT NULL
GROUP BY
	post.ID
;
