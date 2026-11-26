<section class="teacher-panel section-wrap" aria-labelledby="teacher-panel-title">
    <div class="section-heading">
        <div>
            <p class="eyebrow">OPETTAJALLE</p>
            <h2 id="teacher-panel-title">Omat kysymyspaketit</h2>
        </div>
        <p>Luo uusi kysymyspaketti tai poista itse luomasi paketti.</p>
    </div>

    <form class="package-form" id="package-form">
        <label>
            Paketin nimi
            <input name="nimi" type="text" maxlength="150" required>
        </label>
        <label>
            Kuvaus
            <textarea name="kuvaus" rows="3"></textarea>
        </label>
        <label>
            Aihealue
            <select name="aihealue_id" id="package-topic">
                <option value="">Ei aihealuetta</option>
            </select>
        </label>
        <label class="package-publish">
            <input name="julkaistu" type="checkbox" value="1">
            Julkaise paketti heti
        </label>
        <button class="button button-primary" type="submit">Luo kysymyspaketti</button>
    </form>

    <p class="teacher-panel-message" id="teacher-panel-message" role="status" aria-live="polite"></p>
    <div class="teacher-package-list" id="teacher-package-list">
        <p>Haetaan omia kysymyspaketteja...</p>
    </div>
</section>

<script>
    (() => {
        const form = document.getElementById("package-form");
        const message = document.getElementById("teacher-panel-message");
        const packageList = document.getElementById("teacher-package-list");
        const topicSelect = document.getElementById("package-topic");
        let csrfToken = "";


        
        async function sendRequest(url, options = {}) {
            const response = await fetch(url, options);
            const data = await response.json();
            if (!response.ok) {
                throw new Error(data.virhe || "Toiminto epäonnistui.");
            }
            return data;
        }

        function showMessage(text) {
            message.textContent = text;
        }

        function renderPackages(packages) {
            packageList.replaceChildren();

            if (packages.length === 0) {
                packageList.innerHTML = "<p>Et ole vielä luonut kysymyspaketteja.</p>";
                return;
            }

            packages.forEach((quizPackage) => {
                const item = document.createElement("article");
                item.className = "teacher-package";

                const title = document.createElement("h3");
                title.textContent = quizPackage.nimi;

                const details = document.createElement("p");
                details.textContent = `${quizPackage.kuvaus || quizPackage.aihealue || "Ei kuvausta"} · ${Number(quizPackage.kysymys_maara)} kysymystä · ${Number(quizPackage.julkaistu) === 1 ? "Julkaistu" : "Luonnos"}`;

                const deleteButton = document.createElement("button");
                deleteButton.className = "button button-secondary";
                deleteButton.type = "button";
                deleteButton.textContent = "Poista";
                deleteButton.addEventListener("click", async () => {
                    if (!window.confirm(`Poistetaanko kysymyspaketti "${quizPackage.nimi}"?`)) {
                        return;
                    }

                    deleteButton.disabled = true;
                    try {
                        await sendRequest("api.php?action=poista_paketti", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8",
                                "X-CSRF-Token": csrfToken
                            },
                            body: new URLSearchParams({ paketti_id: quizPackage.id })
                        });
                        showMessage("Kysymyspaketti poistettiin.");
                        await loadPackages();
                    } catch (error) {
                        showMessage(error.message);
                        deleteButton.disabled = false;
                    }
                });

                item.append(title, details, deleteButton);
                packageList.appendChild(item);
            });
        }

        async function loadPackages() {
            try {
                const data = await sendRequest("api.php?action=omat_paketit");
                csrfToken = data.csrf_token;
                topicSelect.querySelectorAll("option:not(:first-child)").forEach((option) => option.remove());
                data.aihealueet.forEach((aihealue) => {
                    const option = document.createElement("option");
                    option.value = aihealue.id;
                    option.textContent = aihealue.nimi;
                    topicSelect.appendChild(option);
                });
                renderPackages(data.packages);
            } catch (error) {
                showMessage(error.message);
                packageList.innerHTML = "<p>Kysymyspaketteja ei voitu hakea.</p>";
            }
        }

        form.addEventListener("submit", async (event) => {
            event.preventDefault();
            const submitButton = form.querySelector('button[type="submit"]');
            submitButton.disabled = true;

            try {
                await sendRequest("api.php?action=luo_paketti", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8",
                        "X-CSRF-Token": csrfToken
                    },
                    body: new URLSearchParams(new FormData(form))
                });
                form.reset();
                showMessage("Kysymyspaketti luotiin.");
                await loadPackages();
            } catch (error) {
                showMessage(error.message);
            } finally {
                submitButton.disabled = false;
            }
        });

        loadPackages();
    })();
    UPDATE kayttaja
SET salasana_hash = 'salasana'
WHERE nimi = 'opettaja';
</script>
