------------------------------------------------------------------------------------------------
----------------------------			Сущность: НГП			----------------------------
------------------------------------------------------------------------------------------------

--DROP TABLE IF EXISTS ekbd.ngp CASCADE

BEGIN;

CREATE TABLE IF NOT EXISTS ekbd.ngp (
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
    CONSTRAINT ngp_vid_fkey FOREIGN KEY (vid) REFERENCES ekbd.ngp,  
	CONSTRAINT ngp_dic_ngp_type_fkey FOREIGN KEY (ngp_type_id) REFERENCES ekbd.dic_ngp_type(id)
);

CREATE INDEX IF NOT EXISTS idx_ngp ON ekbd.ngp USING gist (geom) TABLESPACE pg_default;

CREATE OR REPLACE TRIGGER ngp_mdate_update BEFORE UPDATE ON ekbd.ngp
    FOR EACH ROW
    EXECUTE PROCEDURE ekbd.f_update_mdate();

--ALTER TABLE IF EXISTS ekbd.ngp OWNER to ebd;
REVOKE ALL ON TABLE ekbd.ngp FROM ebd_integro;
GRANT ALL ON TABLE ekbd.ngp TO ebd;
GRANT SELECT ON TABLE ekbd.ngp TO ebd_integro;

COMMENT ON TABLE ekbd.ngp IS 'НГП';
COMMENT ON COLUMN ekbd.ngp.name IS 'Название';
COMMENT ON COLUMN ekbd.ngp.ngp_type_id IS 'Тип';
COMMENT ON COLUMN ekbd.ngp.index_all IS 'Индекс';
COMMENT ON COLUMN ekbd.ngp.comment IS 'Примечание';
COMMENT ON COLUMN ekbd.ngp.geom IS 'Геометрия';
COMMENT ON COLUMN ekbd.ngp.cdate IS 'Дата создания';
COMMENT ON COLUMN ekbd.ngp.mdate IS 'Дата модификации/обновления';

END;