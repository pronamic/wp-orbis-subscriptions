--
-- https://mothereff.in/html-entities
--

UPDATE
	orbis_subscription_types AS product
SET
	product.name = REPLACE( product.name, '&#8211;', '–' )
;
