 <?php
 require_once "tietokanta.php";

 $pakettiId = (int)($_GET['paketti_id'] ?? 0);
 $packageName = "Kysymyspaketti";
 $pageError = null;

 if ($pakettiId <= 0) {
     $pageError = "Valitse ensin kysymyspaketti.";
 } else {
     try {
         $stmt = $conn->prepare(
             'SELECT nimi
              FROM kysymyspaketti
              WHERE id = ? AND julkaistu = 1
              LIMIT 1'
         );
         $stmt->execute([$pakettiId]);
         $package = $stmt->fetch();

         if (!$package) {
             $pageError = "Kysymyspaketti ei ole julkinen tai sitä ei löydy.";
         } else {
             $packageName = $package['nimi'];
         }
     } catch (Throwable $e) {
         error_log($e->getMessage());
         $pageError = "Kysymyspaketin tietoja ei voitu ladata.";
     }
 }
 ?>
 
 <!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($packageName, ENT_QUOTES, 'UTF-8') ?> - Kysymys kuuluu</title>
    <link rel="stylesheet" href="quiz.css">
</head>
<body>
    <main>
        <?php include "header1.php"; ?>
        <h1 class="quiz-package-title"><?= htmlspecialchars($packageName, ENT_QUOTES, 'UTF-8') ?></h1>
        <?php if ($pageError): ?>
            <div class="game-quiz-container">
                <p><?= htmlspecialchars($pageError, ENT_QUOTES, 'UTF-8') ?></p>
                <a class="button" href="index.php">Palaa etusivulle</a>
            </div>
        <?php else: ?>
        <!-- creating a modal for when quiz ends -->
        <div class="modal-container" id="score-modal">

            <div class="modal-content-container">

                <h1>Congratulations, Quiz Completed.</h1>

                <div class="grade-details">
                    <p>Attempts : 3</p>
                    <p>Wrong Answers : <span id="wrong-answers"></span></p>
                    <p>Right Answers : <span id="right-answers"></span></p>
                    <p>Grade : <span id="grade-percentage"></span>%</p>
                    <p ><span id="remarks"></span></p>
                </div>

                <div class="modal-button-container">
                    <button onclick="closeScoreModal()">Continue</button>
                </div>

            </div>
        </div>
<!-- end of modal of quiz details-->

        <div class="game-quiz-container">

            <div class="game-details-container">
                <h1>Score : <span id="player-score"></span> / <span id="question-total"></span></h1>
                <h1>Question : <span id="question-number"></span> / <span id="question-total-top"></span></h1>
            </div>

            <div class="game-question-container">
                <h1 id="display-question"></h1>
            </div>

            <div class="game-options-container">

               <div class="modal-container" id="option-modal">

                    <div class="modal-content-container">
                         <h1>Please Pick An Option</h1>

                         <div class="modal-button-container">
                            <button onclick="closeOptionModal()">Continue</button>
                        </div>

                    </div>

               </div>

                <span>
                    <input type="radio" id="option-one" name="option" class="radio" value="optionA" />
                    <label for="option-one" class="option" id="option-one-label"></label>
                </span>


                <span>
                    <input type="radio" id="option-two" name="option" class="radio" value="optionB" />
                    <label for="option-two" class="option" id="option-two-label"></label>
                </span>


                <span>
                    <input type="radio" id="option-three" name="option" class="radio" value="optionC" />
                    <label for="option-three" class="option" id="option-three-label"></label>
                </span>


                <span>
                    <input type="radio" id="option-four" name="option" class="radio" value="optionD" />
                    <label for="option-four" class="option" id="option-four-label"></label>
                </span>


            </div>

            <div class="next-button-container">
                <button onclick="handleNextQuestion()">Next Question</button>
            </div>

        </div>
        <?php endif; ?>
    </main>
    <?php if (!$pageError): ?>
        <script src="quiz.js"></script>
    <?php endif; ?>
</body>
</html>