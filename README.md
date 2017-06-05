# dolibarr_page_from_SQLtable
Generate Pages from SQL Table (imporvement from the dolibarr tools)


# Functionnalities
 - generate a page with view / doc / list almost working
 - will create readable select when a variable start with fk_ 
    - nomenclatue : fk_tableName, if start with fk_user_XXXX user dropdowx list will be fetched
    - try to show the fields rowid & description ( select_generic / print_generic willl need to be addapted to shows other value )
- will create select if the table has enum
- support searchbox for any field (need to had the URL of ajaxGenericSelectHandler.php as 12th parma of selet enum


# Change log

