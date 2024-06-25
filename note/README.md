# Start lorr

dotenv
mv api/env api/.env

start serveur:
SERVER_NAME=localhost docker compose up

url: 
localhost:8080 (/docs pour la visualisation api)
(si besoin port a changer dans docker-compose.yml:51 et api/docker/caddy/Caddyfile:9)

creation des clefs jwt
docker compose exec api_php   php bin/console lexik:jwt:generate-keypair

creation database:
docker compose exec api_php php bin/console doctrine:schema:create

creation orga/admin cap3c:
docker compose exec api_php php bin/console app:init_base

acceder a postgres:
docker compose exec api_database psql app app

postman (cheminement d'une base presque vide a une recuperation des donnees):
    {{DN}} : url de l'api, normallement http://localhost:8080




auth admin cap3c (username inutile)                         -> POST auth_admin cap3c

creer support (seul role a pouvoir creer un organisme)      -> POST create_support

auth support                                                -> POST auth_support

creer organisme (l'admin se cree en meme temps)             -> POST new_orga

auth admin_orga                                             -> POST auth_admin

creer user                                                  -> POST create_user     (save id)

creer description (forme de la donnee savegarder)           -> POST IntAndStr       (save categorie)

optionnel completer decription (peut etre reutiliser)       -> POST IntAnsStr2      (use desc_categorie)

creer table (creation de table correspondant a description) -> POST create_table    (use desc_categorie dans body) (save id)

creer permission (donne a un user de l'organisme un accee aux donnee d'une table)
(value correspond a la permission donnee pour chaque description de la table en binaire en fonction du CRUD.
ex: '4e' -> 0100 1110 -> _R__ CRU_ -> il y a 2 elements dans la decription et l'utilisateur peut lire le premier et ajouter, lire et mettre a jour le second. ou l'inverse) 
(l'admin n'a pas accee aux donnees des tables)              -> POST new permission   (use user_id et table_id)

auth user                                                   -> POST auth_user

ajouter des donnees (tableDynamique)                        -> POST test_table_dyn (use table_id et desc_name)
    (les donnees table_id et desc_name peuvent se recuperer dans GET permission)

recuperer des donnees                                       -> GET test_table_dyn



pour plus de detail sur les donnees voir permission.txt (il peux y avoir quelque difference)

