----------------------------------------------------------------------------------------
------------------------------------    Связи      ------------------------------------- 
----------------------------------------------------------------------------------------

BEGIN;

--
-- Линцензия <--> Полезное ископаемое (license_pi)
--
CREATE TABLE IF NOT EXISTS ekbd.rel_license_pi (
	id uuid UNIQUE NOT NULL DEFAULT uuid_generate_v4() PRIMARY KEY,
	license_id uuid REFERENCES ekbd.license(id),
  	pi_id uuid REFERENCES ekbd.dic_pi(id),
	cdate timestamp DEFAULT now()
);
--ALTER TABLE IF EXISTS ekbd.rel_license_pi OWNER to ebd;
REVOKE ALL ON TABLE ekbd.rel_license_pi FROM ebd_integro;
GRANT ALL ON TABLE ekbd.rel_license_pi TO ebd;
GRANT SELECT ON TABLE ekbd.rel_license_pi TO ebd_integro;
COMMENT ON TABLE ekbd.rel_license_pi IS 'Связь: Лицензии <--> Полезные ископаемые';

--
--Konkurs*
--
-- Участки, предлагаемые к лицензированию <--> Полезное ископаемое (konkurs_pi)
--
CREATE TABLE IF NOT EXISTS ekbd.rel_konkurs_pi (
	id uuid UNIQUE NOT NULL DEFAULT uuid_generate_v4() PRIMARY KEY,
	konkurs_id uuid REFERENCES ekbd.konkurs(id),
  	pi_id uuid REFERENCES ekbd.dic_pi(id),
	cdate timestamp DEFAULT now()
);
--ALTER TABLE IF EXISTS ekbd.rel_konkurs_pi OWNER to ebd;
REVOKE ALL ON TABLE ekbd.rel_konkurs_pi FROM ebd_integro;
GRANT ALL ON TABLE ekbd.rel_konkurs_pi TO ebd;
GRANT SELECT ON TABLE ekbd.rel_konkurs_pi TO ebd_integro;
COMMENT ON TABLE ekbd.rel_konkurs_pi IS 'Связь: Участки, предлагаемые к лицензированию <--> Полезное ископаемое';

END;