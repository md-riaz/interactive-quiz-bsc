<?php
session_start();

// Handle username input
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'])) {
    $_SESSION['username'] = $_POST['username'];
    $_SESSION['currentQuestionIndex'] = 0;
    $_SESSION['answers'] = [];
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Check if username is set in session
if (!isset($_SESSION['username'])) {
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Interactive Quiz App</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 20px;
                background-color: #f9f9f9;
                color: #333;
            }

            .quiz-container {
                max-width: 600px;
                margin: auto;
                padding: 20px;
                background: #fff;
                border-radius: 10px;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            }

            h1 {
                text-align: center;
                color: #4CAF50;
            }

            label {
                display: block;
                margin-bottom: 10px;
                font-weight: bold;
            }

            input[type="text"] {
                width: 96%;
                padding: 10px;
                margin-bottom: 20px;
                border: 1px solid #ccc;
                border-radius: 5px;
            }

            button {
                display: block;
                width: 100%;
                padding: 10px 20px;
                border: none;
                background-color: #4CAF50;
                color: #fff;
                font-size: 16px;
                cursor: pointer;
                border-radius: 5px;
            }

            button:hover {
                background-color: #45a049;
            }

            .text-center {
                text-align: center;
            }

            img {
                max-width: 100%;
            }
        </style>
    </head>

    <body>
        <div class="quiz-container">
            <div class="text-center">
                <img src="./pundra.png" alt="pundra" height="100">
            </div>
            <h1>Welcome to the Interactive Quiz</h1>
            <form method="POST" action="">
                <label for="username">Enter your username:</label>
                <input type="text" id="username" name="username" required maxlength="10">
                <button type="submit">Start Quiz</button>
            </form>

            <div class="text-center" style="color: grey;margin-top: 50px;">
                <span>Developed by <a href="https://github.com/md-riaz" target="_blank">Md. Riaz</a></span>
                <span>Roll 24,</span>
                <span>4th Semester</span>
                <div style="margin: 5px;">Department of Computer Science &amp; Engineering</div>
            </div>
        </div>
    </body>

    </html>
<?php
    exit;
}

// check answer post request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['questionIndex'])) {
    $questionIndex = intval($_POST['questionIndex']);
    $selectedAnswer = $_POST['selectedAnswer'];

    // Get the question from the API response
    $questionData = $_SESSION['questions'][$questionIndex];

    // Prepare options (correct + incorrect answers)
    $options = array_merge([$questionData['correct_answer']], $questionData['incorrect_answers']);

    // Get the correct answer
    $correctAnswer = $questionData['correct_answer'];

    // Check if the selected answer is correct
    $isCorrect = $selectedAnswer === $correctAnswer;

    // Update score in session
    if (!isset($_SESSION['score'])) {
        $_SESSION['score'] = 0;
    }
    if ($isCorrect) {
        $_SESSION['score']++;
    }

    // Store the user's answer
    $_SESSION['answers'][$questionIndex] = $selectedAnswer;

    // Update current question index in session
    $_SESSION['currentQuestionIndex'] = $questionIndex + 1;

    // Prepare response
    $response = [
        "correct" => $isCorrect,
        "correctAnswer" => $correctAnswer,
        "score" => $_SESSION['score']
    ];

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Questions array
if (!isset($_SESSION['questions'])) {
    $questions = file_get_contents('https://opentdb.com/api.php?amount=20&category=18');
    $questions = json_decode($questions, true);
    $apiResponse = $questions['results'];

    // randomize and add to session
    shuffle($apiResponse);
    $_SESSION['questions'] = $apiResponse;
}

if (!isset($_SESSION['score'])) {
    $_SESSION['score'] = 0;
}
$currentQuestionIndex = $_SESSION['currentQuestionIndex'];
$answers = $_SESSION['answers'];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interactive Quiz with Thanos Snap</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/jadenguitarman/thanosjs@1.2.0/thanos.min.js"></script>
    <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f9f9f9;
            color: #333;
        }

        .quiz-container {
            max-width: 600px;
            min-height: 350px;
            margin: auto;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #4CAF50;
        }

        .question {
            display: none;
        }

        .question.active {
            display: block;
        }

        .feedback {
            margin-top: 10px;
            font-weight: bold;
        }

        .feedback.correct {
            color: green;
        }

        .feedback.wrong {
            color: red;
        }

        button {
            display: block;
            margin: 20px auto;
            padding: 10px 20px;
            border: none;
            background-color: #4CAF50;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
        }

        button:hover {
            background-color: #45a049;
        }

        .score {
            text-align: center;
            font-size: 18px;
            margin-bottom: 20px;
        }

        .score span {
            font-weight: bold;
            color: #4CAF50;
        }

        .logout-button {
            text-align: center;
            margin-top: 20px;
        }

        .logout-button button {
            background-color: #f44336;
        }

        .logout-button button:hover {
            background-color: #e53935;
        }

        .text-center {
            text-align: center;
        }

        img {
            max-width: 100%;
        }
    </style>
</head>

<body>
    <div class="quiz-container">

        <div class="text-center">
            <img src="./pundra.png" alt="pundra" height="100">
        </div>

        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <div class="score">Score: <span id="score"><?php echo $_SESSION['score']; ?></span> / <span id="totalQuestions">0</span></div>
        <div id="container">
            <?php

            // Render questions in the DOM
            foreach ($_SESSION['questions'] as $index => $question) {
                $isActive = $index == $currentQuestionIndex ? 'active' : '';
                echo "<div class='question $isActive' data-index='$index'>";
                echo "<p>" . ($index + 1) . ". " . ($question['question']) . "</p>";

                // Check if the question type is boolean
                if ($question['type'] === 'boolean') {
                    $options = ['True', 'False'];
                } else {
                    $options = array_merge([$question['correct_answer']], $question['incorrect_answers']);
                    shuffle($options);  // Randomize options order if you want
                }

                foreach ($options as $key => $option) {
                    $isChecked = isset($answers[$index]) && $answers[$index] == $option ? 'checked' : '';
                    echo "<label>
                            <input type='radio' name='question_$index' value='" . htmlspecialchars($option) . "' $isChecked> " . htmlspecialchars($option) . "
                          </label><br>";
                }
                echo "<div class='feedback'></div>";
                echo "<button class='next-button' style='display: none;'>Next</button>";
                echo "</div>";
            }
            ?>
        </div>
        <div class="logout-button">
            <form method="GET" action="">
                <button type="submit" name="logout">Quit</button>
            </form>
        </div>

        <div class="text-center" style="color: grey;margin-top: 50px;">
            <span>Developed by <a href="https://github.com/md-riaz" target="_blank">Md. Riaz</a></span>
            <span>Roll 24,</span>
            <span>4th Semester</span>
            <div style="margin: 5px;">Department of Computer Science &amp; Engineering</div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            let currentQuestionIndex = <?php echo $currentQuestionIndex; ?>;
            let correctAnswersCount = <?php echo $_SESSION['score']; ?>;
            const totalQuestions = $(".question").length;

            // Update total questions count
            $("#totalQuestions").text(totalQuestions);

            function showQuestion(index) {
                $(".question").removeClass("active");
                $(`.question[data-index="${index}"]`).addClass("active");

                $(".logout-button").show();
            }

            function snapCurrentQuestion() {
                const currentQuestion = $(`.question[data-index="${currentQuestionIndex}"]`)[0];
                return new Promise((resolve) => {
                    const screenWidth = window.innerWidth;
                    const screenHeight = -window.innerHeight;
                    const thanos = new Thanos({
                        victim: currentQuestion,
                        container: document.querySelector(".quiz-container"),
                    });
                    thanos.snap();

                    setTimeout(() => {
                        resolve();
                    }, 5500);
                });
            }

            function checkAnswer(questionIndex, selectedAnswer) {

                $(".logout-button").hide();

                return $.ajax({
                    url: "",
                    method: "POST",
                    data: {
                        questionIndex,
                        selectedAnswer
                    },
                    success: function(response) {
                        const feedback = $(`.question[data-index="${questionIndex}"] .feedback`);
                        const nextButton = $(`.question[data-index="${questionIndex}"] .next-button`);
                        if (response.correct) {
                            feedback.text("Correct!").addClass("correct").removeClass("wrong");
                            correctAnswersCount++;
                            // Update score display
                            $("#score").text(correctAnswersCount);
                        } else {
                            feedback.text("Wrong! The correct answer is: " + response.correctAnswer)
                                .addClass("wrong")
                                .removeClass("correct");
                        }
                        nextButton.show();

                        return response;
                    },
                });
            }

            // Show the first question
            showQuestion(currentQuestionIndex);

            // Handle answer selection
            $(".question input[type='radio']").on("change", function() {
                const questionIndex = $(this).closest(".question").data("index");
                const selectedAnswer = $(this).val();
                $(this).closest(".question").find("input[type='radio']").attr("disabled", true);
                $(this).closest(".question").data("selectedAnswer", selectedAnswer);
                $(`.question[data-index="${questionIndex}"] .next-button`).show();
            });

            // Handle next button
            $(".next-button").on("click", function() {
                // disable next button and show processing progress
                $(this).text("Processing...");
                $(this).attr("disabled", true);

                const questionIndex = $(this).closest(".question").data("index");
                const selectedAnswer = $(this).closest(".question").data("selectedAnswer");
                checkAnswer(questionIndex, selectedAnswer).then((result) => {

                    if (result.correct) {
                        setTimeout(() => {
                            currentQuestionIndex++;
                            if (currentQuestionIndex < totalQuestions) {
                                showQuestion(currentQuestionIndex);
                            } else {
                                $(".quiz-container").html(`<h1>Quiz Completed!</h1><p class="text-center">You got ${correctAnswersCount} out of ${totalQuestions} correct.</p> <div class="logout-button"> <form method="GET" action=""> <button type="submit" name="logout">Logout</button> </form> </div>`);
                            }
                        }, 1000);
                    } else {
                        $(".logout-button").hide();

                        snapCurrentQuestion().then(() => {
                            currentQuestionIndex++;
                            if (currentQuestionIndex < totalQuestions) {
                                showQuestion(currentQuestionIndex);
                            } else {
                                $(".quiz-container").html(`<h1>Quiz Completed!</h1><p class="text-center">You got ${correctAnswersCount} out of ${totalQuestions} correct.</p> <div class="logout-button"> <form method="GET" action=""> <button type="submit" name="logout">Logout</button> </form> </div>`);
                            }
                        });
                    }
                });
            });
        });
    </script>
</body>

</html>