# MSSO login plugin for Wordpress

Ez egy wordpress plugin. 
Segítségével az eDemo SSO autentikációs szolgáltatást igénybe véve lehet bejelentkezni a wordpress-be.

SSO szolgáltatás: https://sso.edemokraciagep.org/static/login.html

funkciók
- bejelentkezés SSO szolgáltatás segítségével
- regisztráció SSO szolgáltatás segítségével (független a gyári WP regisztrálási lehetőség beállítástól)
- login widget - sso login, sso regisztráció, logout, sso account bind, show user profil
- login oldal SSO kiegészítés - sso login, sso regisztráció
- SSO adatok a felhasználói profil oldalon - adatfrissítés funkció
- plugin beállító adminisztrációs panel a beállítások menüben
- felhasználói fiók letiltása (ban user) funkció
- SSO tanusítások user meta
- SSO user ID user mata
- 0 szintű 'SSO user' szerepkör
- assurance függő regisztráció
- shortcode login gomb létrehozásához bejelentkezett usertől függő class atributumokkal
[sso_login_button logged_in_class='hide is you want' logged_out_class='my butyfull button']BEJELENTKEZÉS[/sso_login_button]

opciók
- SSO regisztráció letiltása -> scope: all user
- SSO bejelentkezés letiltása -> scope: all user
- SSL tanusítvány ellenőrzése
- SSO account bind engedélyezése
- admin bar alapértelmezett kikapcsolása
- alapértelmezett felhasználói szerepkör beállítása
- regisztrációhoz szükséges assurancok beállítása

---
