"use strict"

function start(){
  let socket = new WebSocket('wss://dev.musbooking.com/changes?guest=1')

	socket.onopen = () => console.log("Соединение установлено.")

	socket.onclose = event => {
	  console.log(`Обрыв соединения; Код: ${event.code}.`)

	  setTimeout(() => start(), 5000)
	}

	socket.onmessage = event => {
		console.log("Получены данные:", event.data)

		jQuery.ajax({
			url: '/wp-admin/admin-ajax.php',
		    type: "POST",
		    data: {data: JSON.parse(event.data), action: 'socket'}
		})
	}

	socket.onerror = error => console.log("Ошибка", error)
}

// start();
