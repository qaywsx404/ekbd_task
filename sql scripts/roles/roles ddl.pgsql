----------------------------------------------------------------------------------------
---------------------------------- Роли -----------------------------------------------
----------------------------------------------------------------------------------------
  
CREATE ROLE ebd WITH
  LOGIN
  SUPERUSER
  INHERIT
  NOCREATEDB
  NOCREATEROLE
  NOREPLICATION
  ENCRYPTED PASSWORD '';
  
CREATE ROLE ebd_integro WITH
  LOGIN
  NOSUPERUSER
  INHERIT
  NOCREATEDB
  NOCREATEROLE
  NOREPLICATION
  ENCRYPTED PASSWORD '';