SELECT
	subscription.id AS subscription_id,
	subscription.name AS subscription_name,
	subscription.email AS subscription_email,
	post.ID AS subscription_post_id,
	post.post_title AS subscription_post_title,
	subscription_meta_person.meta_value AS person_post_id,
	person_post.post_title AS person_name,
	person_meta.meta_value AS person_email,
	company.name AS company_name,
	company.post_id AS company_post_id,
	company_meta.meta_value AS company_email,
	COALESCE( subscription.email, person_meta.meta_value, company_meta.meta_value ) AS email,
	subscription.cancel_date IS NULL AS active
FROM
	orbis_subscriptions AS subscription
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
	wp_posts AS person_post
			ON person_post.ID = subscription_meta_person.meta_value
		LEFT JOIN
	orbis_companies AS company
			ON company.id = subscription.company_id
		LEFT JOIN
	wp_postmeta AS company_meta
			ON company_meta.post_id = company.post_id AND company_meta.meta_key = "_orbis_company_email_address"
WHERE
	post.post_type = "orbis_subscription"
GROUP BY
	post.ID
;
