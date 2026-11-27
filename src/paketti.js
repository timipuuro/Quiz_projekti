let indexPackages = [];
let packageSearchRequestId = 0;

async function loadIndexPackages() {
            const grid = document.getElementById("package-grid");
            
            try {
                const response = await fetch("api.php?action=paketit");
                if (!response.ok) {
                    throw new Error("Pakettien lataus epäonnistui");
                }

                const data = await response.json();
                const packages = data.packages || [];
                indexPackages = packages;
                grid.innerHTML = "";

                if (packages.length === 0) {
                    grid.innerHTML = "<p>Julkaistuja kysymyspaketteja ei löytynyt.</p>";
                    return;
                }

                packages.forEach((quizPackage) => {
                    grid.appendChild(createPackageCard(quizPackage));
                });
            } catch (error) {
                console.error(error);
                grid.innerHTML = "<p>Kysymyspaketteja ei voitu ladata tietokannasta.</p>";
            }
        }

        function escapePackageHtml(value) {
            const element = document.createElement("span");
            element.textContent = String(value);
            return element.innerHTML;
        }

        function createPackageCard(quizPackage) {
            const link = document.createElement("a");
            link.href = `quiz.php?paketti_id=${encodeURIComponent(quizPackage.id)}`;
            link.innerHTML =
                `<article class="topic-card">
                    <span class="topic-icon topic-icon-purple" aria-hidden="true">✦</span>
                    <h3>${escapePackageHtml(quizPackage.nimi)}</h3>
                    <p>${escapePackageHtml(quizPackage.kuvaus || quizPackage.aihealue || "")}</p>
                    <p>${Number(quizPackage.kysymys_maara)} kysymystä</p>
                    <span class="topic-arrow" aria-hidden="true">↗</span>
                </article>`;
            return link;
        }

        async function pakettihaku() {
            const input = document.getElementById("paketti-haku-kenttä");
            const resultList = document.getElementById("haku-paketti");
            const searchValue = input.value.trim();
            const requestId = ++packageSearchRequestId;

            resultList.innerHTML = "";

            if (searchValue === "") {
                const message = document.createElement("li");
                message.textContent = "Anna kysymyspaketin tunnus.";
                resultList.appendChild(message);
                input.focus();
                
                document.getElementById("package-grid").innerHTML = "";
                indexPackages.forEach((quizPackage) => {
                    document.getElementById("package-grid").appendChild(createPackageCard(quizPackage));
                });
                return;
            }


            if (indexPackages.length === 0) {
                try {
                    const response = await fetch("api.php?action=paketit");
                    if (!response.ok) {
                        throw new Error("Pakettien lataus epäonnistui");
                    }
                    const data = await response.json();
                    if (requestId !== packageSearchRequestId) {
                        return;
                    }
                    indexPackages = data.packages || [];
                } catch (error) {
                    if (requestId !== packageSearchRequestId) {
                        return;
                    }
                    console.error(error);
                    const message = document.createElement("li");
                    message.textContent = "Kysymyspaketteja ei voitu hakea.";
                    resultList.appendChild(message);
                    return;
                }
            }

            const packageId = Number(searchValue);
            const matches = indexPackages.filter((quizPackage) => {
                if (Number.isInteger(packageId) && packageId > 0) {
                    return Number(quizPackage.id) === packageId;
                }
                return String(quizPackage.nimi).toLocaleLowerCase("fi").includes(searchValue.toLocaleLowerCase("fi"));
            });

            if (matches.length === 0) {
                const message = document.createElement("li");
                message.textContent = "Kysymyspakettia ei löytynyt.";
                resultList.appendChild(message);
                document.getElementById("package-grid").innerHTML =
                    "<p>Hakutunnuksella ei löytynyt kysymyspakettia.</p>";
                return;
            }

            const grid = document.getElementById("package-grid");
            grid.innerHTML = "";
            matches.forEach((quizPackage) => {
                grid.appendChild(createPackageCard(quizPackage));
            });
        }

        const searchForm = document.getElementById("paketti-haku-lomake");
        if (searchForm) {
            searchForm.addEventListener("submit", (event) => {
                event.preventDefault();
                pakettihaku();
            });
        }

        loadIndexPackages();

async function loadIndexPackages() {
            const grid = document.getElementById("package-grid");
            
            try {
                const response = await fetch("api.php?action=paketit");
                if (!response.ok) {
                    throw new Error("Pakettien lataus epäonnistui");
                }

                const data = await response.json();
                const packages = data.packages || [];
                indexPackages = packages;
                grid.innerHTML = "";

                if (packages.length === 0) {
                    grid.innerHTML = "<p>Julkaistuja kysymyspaketteja ei löytynyt.</p>";
                    return;
                }

                packages.forEach((quizPackage) => {
                    grid.appendChild(createPackageCard(quizPackage));
                });
            } catch (error) {
                console.error(error);
                grid.innerHTML = "<p>Kysymyspaketteja ei voitu ladata tietokannasta.</p>";
            }
        }

        function escapePackageHtml(value) {
            const element = document.createElement("span");
            element.textContent = String(value);
            return element.innerHTML;
        }

        function createPackageCard(quizPackage) {
            const link = document.createElement("a");
            link.href = `quiz.php?paketti_id=${encodeURIComponent(quizPackage.id)}`;
            link.innerHTML =
                `<article class="topic-card">
                    <span class="topic-icon topic-icon-purple" aria-hidden="true">✦</span>
                    <h3>${escapePackageHtml(quizPackage.nimi)}</h3>
                    <p>${escapePackageHtml(quizPackage.kuvaus || quizPackage.aihealue || "")}</p>
                    <p>${Number(quizPackage.kysymys_maara)} kysymystä</p>
                    <span class="topic-arrow" aria-hidden="true">↗</span>
                </article>`;
            return link;
        }
function hakutyhjennys(){
    packageSearchRequestId++;
    document.getElementById("paketti-haku-kenttä").value="";
    document.getElementById("haku-paketti").innerHTML="";
}
