----------------------------------------------------------------------------------------
---------------------------------- БД ---------------------------------------------
----------------------------------------------------------------------------------------
  
CREATE DATABASE ebd_msk
    WITH
    OWNER = ebd
    ENCODING = 'UTF8'
    LC_COLLATE = 'ru_RU.utf8'
    LC_CTYPE = 'ru_RU.utf8'
    TABLESPACE = pg_default
    CONNECTION LIMIT = -1
	TEMPLATE = template0; --?
    --IS_TEMPLATE = False;

GRANT TEMPORARY, CONNECT ON DATABASE ebd_msk TO PUBLIC;
GRANT ALL ON DATABASE ebd_msk TO ebd;
GRANT CONNECT ON DATABASE ebd_msk TO ebd_integro;

CREATE EXTENSION IF NOT EXISTS postgis SCHEMA public VERSION "3.4.0"; --версия может отличаться
CREATE EXTENSION IF NOT EXISTS "uuid-ossp" SCHEMA public VERSION "1.1";  --версия может отличаться