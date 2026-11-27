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

        <div class="question-editor">
            <div class="question-editor-heading">
                <h3>Kysymykset</h3>
                <button class="button button-secondary" id="add-question" type="button">Lisää kysymys</button>
            </div>
            <div id="question-list"></div>
        </div>

        <button class="button button-secondary" type="submit">Luo kysymyspaketti ja kysymykset</button>
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
        const questionList = document.getElementById("question-list");
        const addQuestionButton = document.getElementById("add-question");
        let csrfToken = "";
        let questionCount = 0;

        function addQuestion() {
            questionCount++;
            const card = document.createElement("fieldset");
            card.className = "question-card";

            card.innerHTML = `
                <legend>Kysymys ${questionCount}</legend>
                ${questionCount > 1 ? '<button class="remove-question button button-secondary" type="button">Poista kysymys</button>' : ""}
                <label>
                    Kysymysteksti
                    <input name="question_text" type="text" maxlength="500" required>
                </label>
                <div class="answer-grid">
                    ${[0, 1, 2, 3].map((index) => `
                        <label>
                            <span>Vaihtoehto ${String.fromCharCode(65 + index)}</span>
                            <span class="answer-input">
                                <input name="correct_${questionCount}" type="radio" value="${index}" ${index === 0 ? "required checked" : ""}>
                                <input name="option_${questionCount}_${index}" type="text" maxlength="300" required>
                            </span>
                        </label>
                    `).join("")}
                </div>
                <label>
                    Selite (valinnainen)
                    <textarea name="explanation" rows="2" maxlength="1000"></textarea>
                </label>
            `;

            const removeButton = card.querySelector(".remove-question");
            if (removeButton) {
                removeButton.addEventListener("click", () => {
                    card.remove();
                    renumberQuestions();
                });
            }
            questionList.appendChild(card);
        }

        function renumberQuestions() {
            [...questionList.querySelectorAll(".question-card")].forEach((card, index) => {
                card.querySelector("legend").textContent = `Kysymys ${index + 1}`;
            });
        }

        addQuestionButton.addEventListener("click", addQuestion);

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
                details.textContent = `${quizPackage.kuvaus || quizPackage.aihealue || "Ei kuvausta"} · ${Number(quizPackage.kysymys_maara)} kysymystä · ${Number(quizPackage.julkaistu) === 1 ? "Julkinen" : "Yksityinen"}`;

                const actions = document.createElement("div");
                actions.className = "teacher-package-actions";
                // näkyvyyden vaihto ja poisto
                const visibilityButton = document.createElement("button");
                visibilityButton.className = "button button-secondary";
                visibilityButton.type = "button";
                const isPublic = Number(quizPackage.julkaistu) === 1;
                visibilityButton.textContent = isPublic ? "Muuta yksityiseksi" : "Julkaise";
                visibilityButton.addEventListener("click", async () => {
                    visibilityButton.disabled = true;
                    try {
                        const result = await sendRequest("api.php?action=vaihda_julkisuus", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8",
                                "X-CSRF-Token": csrfToken
                            },
                            body: new URLSearchParams({
                                paketti_id: quizPackage.id,
                                julkaistu: isPublic ? "0" : "1"
                            })
                        });
                        quizPackage.julkaistu = result.julkaistu;
                        showMessage(`Kysymyspaketti on nyt ${result.tila.toLowerCase()}.`);
                        await loadPackages();
                    } catch (error) {
                        showMessage(error.message);
                        visibilityButton.disabled = false;
                    }
                });

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

                actions.append(visibilityButton, deleteButton);
                item.append(title, details, actions);
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
                const questions = [...questionList.querySelectorAll(".question-card")].map((card) => {
                    const optionInputs = [...card.querySelectorAll('input[type="text"][name^="option_"]')];
                    const correctRadio = card.querySelector('input[type="radio"]:checked');
                    return {
                        teksti: card.querySelector('input[name="question_text"]').value.trim(),
                        selite: card.querySelector('textarea[name="explanation"]').value.trim() || null,
                        vaihtoehdot: optionInputs.map((input) => input.value.trim()),
                        oikea_indeksi: correctRadio ? Number(correctRadio.value) : null
                    };
                });

                if (questions.length === 0) {
                    throw new Error("Lisää vähintään yksi kysymys.");
                }

                const body = new URLSearchParams();
                body.set("nimi", form.elements.namedItem("nimi").value);
                body.set("kuvaus", form.elements.namedItem("kuvaus").value);
                body.set("aihealue_id", form.elements.namedItem("aihealue_id").value);
                if (form.elements.namedItem("julkaistu").checked) {
                    body.set("julkaistu", "1");
                }
                body.set("kysymykset", JSON.stringify(questions));

                await sendRequest("api.php?action=luo_paketti", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8",
                        "X-CSRF-Token": csrfToken
                    },
                    body
                });
                form.reset();
                questionList.replaceChildren();
                questionCount = 0;
                addQuestion();
                showMessage("Kysymyspaketti luotiin.");
                await loadPackages();
            } catch (error) {
                showMessage(error.message);
            } finally {
                submitButton.disabled = false;
            }
        });

        addQuestion();
        loadPackages();
    })();
</script>