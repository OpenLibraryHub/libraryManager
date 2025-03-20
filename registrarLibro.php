<?php
session_start();
if(!isset($_SESSION['correo'])){
    header('Location: index.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biblioteca</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" defer=""></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@1.16.1/dist/umd/popper.min.js" defer=""></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" defer=""></script>
    <link rel="shortcut icon" href="https://cdn.pixabay.com/photo/2017/01/31/15/33/linux-2025130_1280.png" type="image/x-icon">
    <link rel="stylesheet" href="style/registrarLibro//registrarLibro.css">
    <link rel="stylesheet" href="style/todos.css">
</head>
<body>

<?php
include('nav.php');
?>

<main class="center-main">


<form action="./back/createLibro.php" id="bookForm" method="POST" class="form-grid">

<label class="label-input" >
    <p class="label-p"> ISBN </p>

    <div class="div-isbn">
    <input class="input-max" id="ISBNInput" type="number" name="LibrosID">
    <button id="searchISBN" type="button" class="btn btn-black search-button">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                          <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                      </svg>
    </button>
    </div>

</label>

<label class="label-input two-span" >
    <p class="label-p"> Titulo </p>
    <input class="input-max book-inputs"  type="text" name="Titulo" required>
</label>

<label class="label-input two-span">
    <p class="label-p"> Autor </p>
    <input class="input-max book-inputs" type="text" name="Autor" required>
</label>

<label class="label-input">
    <p class="label-p"> Clasificación </p>
    <select class="input-max" name="ClasificacionID">
    <?php

      include("back/conexion.php");
      $con=conectar();
      if (mysqli_connect_errno()) {
        echo "Error al conectar a la base de datos: comuniquese con el Ingeniero " . mysqli_connect_error();
        exit();
      }

      $query = "SELECT ClasificacionID id, Descripcion body FROM clasificacion";
      $result = mysqli_query($con, $query);
      $classifications = $result->fetch_all(MYSQLI_ASSOC);
    
    foreach ($classifications as $classification) { ?>

        <option value="<?=$classification["id"]?>" <?=$classification['id'] == "040" ? "selected": ""?>> <?=$classification["body"]?> </option>

    <?php } ?>
    </select>
</label>

<label class="label-input">
    <p class="label-p">Codigo de clasificación</p>
    <input type="text" class="input-max" name="CodigoClasificacion">
</label>

<label class="label-input">
    <p class="label-p">Numero de Ejemplares</p>
    <input type="number" class="input-max book-inputs" name="N_Ejemplares" required>
</label>

<label class="label-input">
    <p class="label-p"> Origen </p>
    <select class="input-max" name="OrigenID">
    <?php
    
      $query1 = "SELECT OrigenID id, Donado_por body FROM origen";
      $result1 = mysqli_query($con, $query1);
      $sources = $result1->fetch_all(MYSQLI_ASSOC);
        print_r($sources);
    foreach ($sources as $source) {  echo $soruce["id"]?>
        <option value="<?=$source["id"]?>"> <?=$source["body"]?> </option>

    <?php } ?>
    </select>
</label>

<label class="label-input">
    <p class="label-p"> Numero de Disponibles </p>
    <input type="number" class="input-max book-inputs" name="N_Disponible" required>

</label>

<label class="label-input">
    <p class="label-p"> Tipo de Etiqueta </p>
    <select class="input-max" name="EtiquetaID" >
    <option value="NULL"> No tiene </option>
    <?php
    
    $query1 = "SELECT EtiquetaID id, CONCAT(Color,' ',Descripcion ) body FROM etiqueta";
    $result1 = mysqli_query($con, $query1);
    $sources = $result1->fetch_all(MYSQLI_ASSOC);
  foreach ($sources as $source) {  echo $soruce["id"]?>
      <option value="<?=$source["id"]?>"> <?=$source["body"]?> </option>

  <?php } ?>
    </select>
</label>

<label class="label-input">
    <p class="label-p"> Sala </p>
    <select class="input-max" name="SalaID" >
    <?php
    
    $query1 = "SELECT SalaID id, Descripcion body FROM sala";
    $result1 = mysqli_query($con, $query1);
    $sources = $result1->fetch_all(MYSQLI_ASSOC);
  foreach ($sources as $source) {  echo $soruce["id"]?>
      <option value="<?=$source["id"]?>"> <?=$source["body"]?> </option>

  <?php } ?>
    </select>
</label>

<label class="label-input two-span">
    <p class="label-p"> Observación </p>
    <textarea class="input-max book-inputs" name="Observacion" required>

    </textarea>

</label>

<label class="label-input ">
    <button class="input-max btn btn-success" id="registerBookButton" type="submit">
        Registrar Libro
    </button>

</label>

</form>

</main>


<dialog id="modal" class="hideModal">
        <form method="POST" action="back/addBook.php" id="dialogForm">
            <menu id="menuModal">
                <div id="bookValueDiv" >

                </div>
                <div id="buttons-div">
                <button id="closeModal" type="button"> Cerrar </button>
                </div>
            </menu>
        </form>
    
</dialog>

<script src="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0/js/bootstrap.min.js"></script>
<script>
const verifyBookModal = document.getElementById('modal');
async function searchForISBN() {
const ISBNValue = document.querySelector("#ISBNInput").value;
    const fetchAddRepetedBook = await fetch("http://localhost/library/back/addRepetedBook.php", {
        method: "POST",
        credentials: "include",
        body: ISBNValue
    });
    const resJson = await fetchAddRepetedBook.json();
    return resJson;
}

const searchISBN = document.querySelector("#searchISBN");
const registerBookButton = document.querySelector("#registerBookButton");

registerBookButton.addEventListener('click', async(e)=> {
    e.preventDefault();
    const bookInputs = Array.from(document.querySelectorAll(".book-inputs"));

    const isbnValue = await searchForISBN();
    const book = document.querySelector("#bookValueDiv");
    let alertText = "";
    if (isbnValue.length === 0 ) {

        for (const [i,input] of bookInputs.entries()) {
            if ( input.value.trim() == "")  {
                if (i === 0 ) alertText = alertText + "Falta el Titulo \n";
                if (i === 1 ) alertText = alertText  + "Falta el Autor \n";
                if (i === 2 ) alertText = alertText + "Falta el Numero de Ejemplares \n";
                if (i === 3 ) alertText = alertText + "Falta el Numero de Disponibles \n";
                if (i === 4 ) alertText = alertText + "Falta la Observacion \n";
            }
        }
        if (alertText.length > 1) { 
            alert(alertText);
        } else {
            document.querySelector('#bookForm').submit();
        }

    } else {

        book.innerHTML = `
                <h2> Se encontro un Libro con ese ISBN con estos datos. </h2>
                <input type="hidden" value="${isbnValue[0].id}" name="LibrosID">
        <table style="width:100%">
    <tr>
        <th>ISBN:</th>
        <td>${isbnValue[0].LibrosID}</td>
    </tr>
    <tr>
        <th>Titulo:</th>
        <td>${isbnValue[0].Titulo}</td>
    </tr>
    <tr>
        <th>Autor:</th>
        <td>${isbnValue[0].Autor}</td>
    </tr>
    <tr>
        <th>Clasificacion:</th>
        <td>${isbnValue[0].ClasificacionID}</td>
    </tr>
    <tr>
        <th>Codigo Clasificacion Completo:</th>
        <td>${isbnValue[0].CodigoClasificacion === null ? "No tiene" : isbnValue[0].CodigoClasificacion }</td>
    </tr>
    <tr>
        <th>Etiqueta:</th>
        <td>${isbnValue[0].EtiquetaID === null ? "No tiene Etiqueta" : isbnValue[0].EtiquetaID }</td>
    </tr>
    <tr>
        <th>Sala:</th>
        <td>${isbnValue[0].sala }</td>
    </tr>
        </table>
        `;
        const buttonsDiv = document.querySelector("#buttons-div");
        const createButton = document.createElement("button");
        createButton.append("Agregar Libro");
        createButton.setAttribute("type", "submit");
        createButton.setAttribute("class", "btn btn-success button-add-book");
        if (document.querySelector('.button-add-book')) {
        } else {
            buttonsDiv.appendChild(createButton);

        }
        verifyBookModal.showModal();
    }
});

document.getElementById('closeModal').addEventListener('click', function() {
    verifyBookModal.close();

});


searchISBN.addEventListener("click", async(e)=> {
    const isbnValue = await searchForISBN();
    const book = document.querySelector("#bookValueDiv");
    if (isbnValue.length === 0 ) {
        book.innerHTML = "<h2> No existen ISBN de ese Libro cierre este dialogo y proceda con la creacion del Libro </h2>";
        if (document.querySelector('.button-add-book')) {
            document.querySelector('.button-add-book').remove();
        }
        verifyBookModal.showModal();
    } else {
        book.innerHTML = `
                <h2> Se encontro un Libro con ese ISBN con estos datos. </h2>
                <input type="hidden" value="${isbnValue[0].id}" name="LibrosID">
        <table style="width:100%">
    <tr>
        <th>ISBN:</th>
        <td>${isbnValue[0].LibrosID}</td>
    </tr>
    <tr>
        <th>Titulo:</th>
        <td>${isbnValue[0].Titulo}</td>
    </tr>
    <tr>
        <th>Autor:</th>
        <td>${isbnValue[0].Autor}</td>
    </tr>
    <tr>
        <th>Clasificacion:</th>
        <td>${isbnValue[0].ClasificacionID}</td>
    </tr>
    <tr>
        <th>Codigo Clasificacion Completo:</th>
        <td>${isbnValue[0].CodigoClasificacion === null ? "No tiene" : isbnValue[0].CodigoClasificacion }</td>
    </tr>
    <tr>
        <th>Etiqueta:</th>
        <td>${isbnValue[0].EtiquetaID === null ? "No tiene Etiqueta" : isbnValue[0].EtiquetaID }</td>
    </tr>
    <tr>
        <th>Sala:</th>
        <td>${isbnValue[0].sala }</td>
    </tr>
        </table>
        `;
        const buttonsDiv = document.querySelector("#buttons-div");
        const createButton = document.createElement("button");
        createButton.append("Agregar Libro");
        createButton.setAttribute("type", "submit");
        createButton.setAttribute("class", "btn btn-success button-add-book");
        if (document.querySelector('.button-add-book')) {
        } else {
            buttonsDiv.appendChild(createButton);

        }
        verifyBookModal.showModal();
    }
});

document.getElementById('closeModal').addEventListener('click', function() {
    verifyBookModal.close();
});



</script>
</body>
</html>