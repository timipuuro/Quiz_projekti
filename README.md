OHJELMISTOPROJEKTIN SUUNNITELMA
Projektin nimi:  quiz
Päivämäärä: 16.11.2026
Versionumero: 0.1.3
Laatija(t): Kasper, Lenni, Mico
(lue editissä)
1.	Yhteenveto ja Esittely

1.1.	Projektin kuvaus
käyttäjäystävällinen kysymyspeli, jossa opettajat voivat hallinnoida kysymyksiä ja opiskelijat testata osaamistaan eri aihealueilla. Sovelluksen tulee sisältää kaksi päätoiminnallisuutta: pelinäkymä opiskelijoille ja hallintapaneeli opettajille. opettajat voivat luoda ja postaa omia kysymyspaketteja. oppilaat voivat hakea ja suorittaa paketteja.
1.2. Projektin tavoitteet
Projekti on toimiva 25.11 mennessä.
1.3. Sidosryhmät
•	Koulu, opettaja ja tiimi

1.4.Vaatimukset.
•	Sivusto toimii virheetömästi.
•	Projekti kuvaus on täytetty.




 
 Tiimin työnjako
Tehtävä	              Kasper	Lenni	Mico
Sprintin suunnittelu	    R      	C  	C
Tietokannan suunnittelu	  R      	c  	I
API-toteutus	            I	      I   R
UI-suunnittelu	          I	      R	  I
Testaus                  	C      	R	  C
Dokumentointi	            R	      I	  I
Demo ja retrospektiivi	  i	      C	  R
R = Responsible, A = Accountable, C = Consulted, I = Informed


2.Projektin aikataulut ja vaiheet.
2.1 Projektin vaiheet
1. Suunnittelu 2. Tietokanta, API ja frontin alku. 3. UI-suunitelu ja testaus. 4. käyttöön otto.
2.2 Tärkeimmät välitavoitteet
Selkeä ja toimiva tietokanta(18.11 ).
 Toimiva API(19.11).
 Tyydyttävä UI ja etusivu(20.11).
 Testaus (23.11).

 2.3 Yksityiskohtainen aikataulu.
Tehtävä	                    v iikko 1.		viikko 2.	  viikko 3.
Sprintin suunnittelu	         valmis		    -	-
Tietokannan suunnittelu	       aloitus k		valmis	-
API-toteutus	                aloitus Mico		 jatko	valmis
UI-suunnittelu	             aloitus Lenni		 jatko	valmis
Testaus	Lenni		                          aloitus		valmis





	


3.Riskien hallinta.

3.1.Riskianalyysi
•	Projekti ei pysy aikataulussa.
•	käyttäjä voi asettaa haitalista koodia kysymyksiin.

3.2 Varasuunnitelma
Tarvittaessa roolit voi muuttua ja aikatauluja voidaan vaihtaa.

4. Viestintä suunnitelma.
4.1 Viestintä kanava.
Teams ja trello
4.2 Kokoukset.
Viikoittaiset edistymis- kokoukset ja Dailyt



4.3 Raportointi.
edistyminen raportoidaan Readme: hin ja kommentteihin. Dayilit kirjataan ylös.


-----------------------ER----------------------
•	 Pakollinen yksi–moni -suhteet, esimerkiksi kysymyspaketti --> kysymys.
•	Valinnainen yksi–moni -suhteet kahdessa kohdassa: aihealue --> kysymyspaketti ja vastausvaihtoehto  pelivastaus. Nämä vastaavat SQL:n valinnaisia viiteavaimia (aihealue_id, valittu_id).

-----------------~Edistyminen~--------------------------------
16.11.26 projekti alkoi.
17.11.26 klo: 8.58 Tietokanta saatiin valmiiksi
17.11.26 klo: 12.40 Api yhteys ja hash on tehty.


-----------------~Tietokannan päivitys~--------------------------------
Jos tietokanta on alustettu ennen salasanan hash-saraketta, suorita kerran
tiedosto migrations/20261125_add_salasana_hash.sql tietokantaan
Luokka_Tietokanta. Uusissa tietokannoissa sarake tulee Luokka_Tietokanta.sql-tiedostosta.


