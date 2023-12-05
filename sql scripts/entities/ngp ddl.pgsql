------------------------------------------------------------------------------------------------
----------------------------			Сущность: НГП			----------------------------
------------------------------------------------------------------------------------------------

--DROP TABLE IF EXISTS ebd_ekbd.ngp CASCADE

BEGIN;

CREATE TABLE IF NOT EXISTS ebd_ekbd.ngp (
	id uuid UNIQUE NOT NULL DEFAULT uuid_generate_v4(),
	gid bigserial,
	vid uuid, 	

	name character varying,
    ngp_type_id uuid,
    index_all character varying,
    				
	comment character varying DEFAULT NULL,					
	src_hash character varying NULL,
	cdate timestamp DEFAULT now(), 				
    mdate timestamp DEFAULT now(),
	geom geometry(MultiPolygon, 987654),

	CONSTRAINT ngp_pkey PRIMARY KEY (id),
    CONSTRAINT ngp_vid_fkey FOREIGN KEY (vid) REFERENCES ebd_ekbd.ngp,  
	CONSTRAINT ngp_dic_ngp_type_fkey FOREIGN KEY (ngp_type_id) REFERENCES ebd_ekbd.dic_ngp_type(id)
);

CREATE INDEX IF NOT EXISTS idx_ngp ON ebd_ekbd.ngp USING gist (geom) TABLESPACE pg_default;

CREATE OR REPLACE TRIGGER ngp_mdate_update BEFORE UPDATE ON ebd_ekbd.ngp
    FOR EACH ROW
    EXECUTE PROCEDURE ebd_ekbd.f_update_mdate();

ALTER TABLE IF EXISTS ebd_ekbd.ngp OWNER to ebd;
REVOKE ALL ON TABLE ebd_ekbd.ngp FROM ebd_integro;
GRANT ALL ON TABLE ebd_ekbd.ngp TO ebd;
GRANT SELECT ON TABLE ebd_ekbd.ngp TO ebd_integro;

COMMENT ON TABLE ebd_ekbd.ngp IS 'НГП';
COMMENT ON COLUMN ebd_ekbd.ngp.name IS 'Название';
COMMENT ON COLUMN ebd_ekbd.ngp.ngp_type_id IS 'Тип';
COMMENT ON COLUMN ebd_ekbd.ngp.index_all IS 'Индекс';
COMMENT ON COLUMN ebd_ekbd.ngp.comment IS 'Примечание';
COMMENT ON COLUMN ebd_ekbd.ngp.geom IS 'Геометрия';
COMMENT ON COLUMN ebd_ekbd.ngp.cdate IS 'Дата создания';
COMMENT ON COLUMN ebd_ekbd.ngp.mdate IS 'Дата модификации/обновления';

END;