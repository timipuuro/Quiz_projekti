


let shuffledQuestions = []
let questions = []
let questionLimit = 10

async function loadQuestions() {
    try {
        const url = new URL("api.php", window.location.href)
        url.searchParams.set("action", "kysymykset")

        const pakettiId = new URLSearchParams(window.location.search).get("paketti_id")
        if (pakettiId) {
            url.searchParams.set("paketti_id", pakettiId)
        }

        const response = await fetch(url)
        if (!response.ok) {
            throw new Error("Kysymysten lataus epäonnistui")
        }

        const data = await response.json()
        questions = data.questions || []
        questionLimit = Math.min(questionLimit, questions.length)
        NextQuestion(0)
    } catch (error) {
        console.error(error)
        document.getElementById("display-question").innerHTML =
            "Kysymyksiä ei voitu ladata tietokannasta."
    }
}

function handleQuestions() {
    if (shuffledQuestions.length > 0) return

    const availableQuestions = [...questions]
    for (let i = availableQuestions.length - 1; i > 0; i--) {
        const randomIndex = Math.floor(Math.random() * (i + 1))
        ;[availableQuestions[i], availableQuestions[randomIndex]] =
            [availableQuestions[randomIndex], availableQuestions[i]]
    }

    shuffledQuestions = availableQuestions.slice(0, questionLimit)
}


let questionNumber = 1 //holds the current question number
let playerScore = 0  //holds the player score
let wrongAttempt = 0 //amount of wrong answers picked by player
let indexNumber = 0 //will be used in displaying next question

// function for displaying next question in the array to dom
//also handles displaying players and quiz information to dom
function NextQuestion(index) {
    handleQuestions()
    const currentQuestion = shuffledQuestions[index]
    if (!currentQuestion) {
        handleEndGame()
        return
    }
    document.getElementById("question-number").innerHTML = questionNumber
    document.getElementById("player-score").innerHTML = playerScore
    document.getElementById("question-total").innerHTML = shuffledQuestions.length
    document.getElementById("question-total-top").innerHTML = shuffledQuestions.length
    document.getElementById("display-question").textContent = currentQuestion.question;
    document.getElementById("option-one-label").textContent = currentQuestion.options[0];
    document.getElementById("option-two-label").textContent = currentQuestion.options[1];
    document.getElementById("option-three-label").textContent = currentQuestion.options[2];
    document.getElementById("option-four-label").textContent = currentQuestion.options[3];

}


function checkForAnswer() {
    const currentQuestion = shuffledQuestions[indexNumber] //gets current Question 
    if (!currentQuestion) return false

    const currentQuestionAnswer = currentQuestion.correctOption //gets current Question's answer
    const options = document.getElementsByName("option"); //gets all elements in dom with name of 'option' (in this the radio inputs)
    let correctOption = null

    options.forEach((option) => {
        if (option.value === currentQuestionAnswer) {
            //get's correct's radio input with correct answer
            correctOption = option.labels[0].id
        }
    })

    //checking to make sure a radio input has been checked or an option being chosen
    if (options[0].checked === false && options[1].checked === false && options[2].checked === false && options[3].checked == false) {
        document.getElementById('option-modal').style.display = "flex"
        return false
    }

    //checking if checked radio button is same as answer
    options.forEach((option) => {
        if (option.checked === true && option.value === currentQuestionAnswer) {
            document.getElementById(correctOption).style.backgroundColor = "green"
            playerScore++ //adding to player's score
            indexNumber++ //adding 1 to index so has to display next question..
            //set to delay question number till when next question loads
            setTimeout(() => {
                questionNumber++
            }, 1000)
        }

        else if (option.checked && option.value !== currentQuestionAnswer) {
            const wrongLabelId = option.labels[0].id
            document.getElementById(wrongLabelId).style.backgroundColor = "red"
            document.getElementById(correctOption).style.backgroundColor = "green"
            wrongAttempt++ //adds 1 to wrong attempts 
            indexNumber++
            //set to delay question number till when next question loads
            setTimeout(() => {
                questionNumber++
            }, 1000)
        }
    })

    return true
}



//called when the next button is called
function handleNextQuestion() {
    if (!checkForAnswer()) return //check if player picked right or wrong option
    unCheckRadioButtons()
    //delays next question displaying for a second just for some effects so questions don't rush in on player
    setTimeout(() => {
        if (indexNumber < shuffledQuestions.length) {
            NextQuestion(indexNumber)
        }
        else {
            handleEndGame()//ends game if index number greater than 9 meaning we're already at the 10th question
        }
        resetOptionBackground()
    }, 1000);
}

//sets options background back to null after display the right/wrong colors
function resetOptionBackground() {
    const options = document.getElementsByName("option");
    options.forEach((option) => {
        document.getElementById(option.labels[0].id).style.backgroundColor = ""
    })
}

// unchecking all radio buttons for next question(can be done with map or foreach loop also)
function unCheckRadioButtons() {
    const options = document.getElementsByName("option");
    for (let i = 0; i < options.length; i++) {
        options[i].checked = false;
    }
}

// function for when all questions being answered
function handleEndGame() {
    let remark = null
    let remarkColor = null

    // condition check for player remark and remark color
    if (playerScore <= 3) {
        remark = "Bad Grades, Keep Practicing."
        remarkColor = "red"
    }
    else if (playerScore >= 4 && playerScore < 7) {
        remark = "Average Grades, You can do better."
        remarkColor = "orange"
    }
    else if (playerScore >= 7) {
        remark = "Excellent, Keep the good work going."
        remarkColor = "green"
    }
    const playerGrade = shuffledQuestions.length
        ? Math.round((playerScore / shuffledQuestions.length) * 100)
        : 0

    //data to display to score board
    document.getElementById('remarks').innerHTML = remark
    document.getElementById('remarks').style.color = remarkColor
    document.getElementById('grade-percentage').innerHTML = playerGrade
    document.getElementById('wrong-answers').innerHTML = wrongAttempt
    document.getElementById('right-answers').innerHTML = playerScore
    document.getElementById('score-modal').style.display = "flex"

}

//closes score modal, resets game and reshuffles questions
function closeScoreModal() {
    questionNumber = 1
    playerScore = 0
    wrongAttempt = 0
    indexNumber = 0
    shuffledQuestions = []
    document.getElementById('score-modal').style.display = "none"
    loadQuestions()
}

//function to close warning modal
function closeOptionModal() {
    document.getElementById('option-modal').style.display = "none"
}

loadQuestions()