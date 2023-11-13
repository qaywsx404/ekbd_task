----------------------------------------------------------------------------------------
---------------------------------- Схемы -----------------------------------------------
----------------------------------------------------------------------------------------
  
CREATE SCHEMA IF NOT EXISTS ebd_gis AUTHORIZATION ebd;
GRANT ALL ON SCHEMA ebd_gis TO ebd;
GRANT USAGE ON SCHEMA ebd_gis TO ebd_integro;

--схема для творчества
CREATE SCHEMA IF NOT EXISTS ebd_ekbd AUTHORIZATION ebd;
GRANT ALL ON SCHEMA ebd_ekbd TO ebd;
GRANT USAGE ON SCHEMA ebd_ekbd TO ebd_integro;