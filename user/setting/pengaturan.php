<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemberitahuan - Website Sedang Dibangun</title>

    <!-- Include Animate.css for animation effects -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <!-- Custom styles for the page -->
    <style>
        body {
            background-color: #f7f7f7;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
            color: #333;
        }
        
        .container {
            text-align: center;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 100%;
        }

        h1 {
            font-size: 2.5rem;
            color: #F15400;
            margin-bottom: 20px;
        }

        p {
            font-size: 1.2rem;
            margin-bottom: 30px;
        }

        .animated-icon {
            font-size: 5rem;
            margin-bottom: 20px;
            color: #F15400;
            animation: bounce 2s infinite;
        }

        .button {
            padding: 12px 25px;
            background-color: #F15400;
            color: white;
            font-size: 1.1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .button:hover {
            background-color: #c44600;
        }

        @keyframes bounce {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-20px);
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="animate__animated animate__fadeInDown">
        <div class="animated-icon">
            🚧
        </div>
        <h1>KARENA KAMU JAWA MAKA PENGATURAN BELUM ADA</h1>
        
        <!-- Audio element -->
        <audio id="audio" src="jawa-jawa.mp3" preload="auto" autoplay></audio>

        <!-- Button to play sound -->
        <button class="button" onclick="playSound()">jajal ulang</button>
    </div>
</div>

<script>
    function playSound() {
    var audio = document.getElementById("audio");

    // Check if the audio can be played
    audio.play().catch(function(error) {
        alert("Terjadi kesalahan dalam memutar audio: " + error.message);
    });

    audio.onended = function() {
        alert('Suara selesai diputar!');
    };
}

</script>

</body>
</html>
