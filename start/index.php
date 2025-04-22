<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Halaman Awal</title>
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: #121212;
      color: white;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      height: 100vh;
      margin: 0;
    }
    h1 {
      font-size: 2.5rem;
      margin-bottom: 20px;
    }
    .typing-text {
      border-right: 2px solid #fff;
      white-space: nowrap;
      overflow: hidden;
      animation: blink-caret 0.75s step-end infinite;
    }
    @keyframes blink-caret {
      from, to { border-color: transparent; }
      50% { border-color: white; }
    }
    button {
      padding: 10px 30px;
      font-size: 1.2rem;
      border: none;
      border-radius: 10px;
      background-color: white;
      color: black;
      cursor: pointer;
      transition: 0.3s ease;
    }
    button:hover {
      background-color: gray;
    }
  </style>
</head>
<body>
  <h1 class="typing-text" id="text"></h1>
  <a href="../home/login.php"><button >MASUK</button></a>

  <script>
     const texts = [
      "Rizal Lazuardi STI202303398",
      "Ilham Aufa Nugroho STI202303346",
      "Tri Wahyu Hidayat STI202303353",
      "Maulana Yusuf STI202303534",
      "Agustian Maldini STI202303460",
      "Muhammad Roy Setiawan STI202303331",
      "Muhammad Syarif Hidayatullah STI202303527",
      "Maresta arsalani STI202203075",
      "Ahda Faizalziddi Al Aziz STI202303600",
      "Zaki Nur Faturrohman STI202303747",
      "Sahrur Anam STI202303552"]
    let count = 0;
    let index = 0;
    let currentText = '';
    let letter = '';

    function type() {
      if (count === texts.length) {
        count = 0;
      }
      currentText = texts[count];
      letter = currentText.slice(0, ++index);

      document.getElementById('text').textContent = letter;
      if (letter.length === currentText.length) {
        setTimeout(() => {
          erase();
        }, 1500);
      } else {
        setTimeout(type, 100);
      }
    }

    function erase() {
      letter = currentText.slice(0, --index);
      document.getElementById('text').textContent = letter;
      if (letter.length === 0) {
        count++;
        setTimeout(type, 200);
      } else {
        setTimeout(erase, 50);
      }
    }


    type(); // Memulai typing saat halaman dibuka
  </script>
</body>
</html>
