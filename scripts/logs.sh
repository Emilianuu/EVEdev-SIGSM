#!/bin/bash

ruta_logs="/var/log/secure"

mostrar_menu(){
         clear
         echo "====================================="
         echo "=          GESTIÓN DE LOGS          ="
         echo "====================================="
         echo ""
         echo "1) Accesos por SSH"
         echo "2) Inicios de sesión"
         echo "3) Comandos sudo"
         echo "4) Salir"
         echo ""
         echo "====================================="
}

pausa(){
        echo ""
        read -p "Presione [Enter] para continuar..."
        echo ""
}

validar_log(){
	if [ ! -f "$ruta_logs" ]; then
		echo "[ERROR] No se encontró el archivo de log ($ruta_logs)."
		return 1
	fi
	return 0
}

accesos_ssh(){
	clear
	echo "=============================================="
	echo "=              ACCESOS POR SSH               ="
	echo "=============================================="
	echo ""

	validar_log || return

	grep -E "sshd.*(Accepted|Failed|Invalid)" "$ruta_logs" | tail -n 25

	echo ""
	echo "=============================================="
	pausa
}

inicios_sesion(){
	clear
	echo "=============================================="
	echo "=             INICIOS DE SESIÓN              ="
	echo "=============================================="
	echo ""

	validar_log || return

	grep -E "session opened for user" "$ruta_logs" | tail -n 25

	echo ""
	echo "=============================================="
	pausa
}

comandos_sudo(){
	clear
	echo "=============================================="
	echo "=              COMANDOS CON SUDO             ="
	echo "=============================================="
	echo ""

	validar_log || return

	grep "sudo:" "$ruta_logs" | grep "COMMAND=" | tail -n 25

	echo ""
	echo "=============================================="
	pausa
}

opcion=999

while [ $opcion != "4" ]; do
	mostrar_menu
	read -p "Seleccione una opción: " opcion
	echo ""
	case $opcion in

		1)
			accesos_ssh
			;;

		2)
			inicios_sesion
			;;

		3)
			comandos_sudo
			;;

		4)
			echo "Saliendo..."
			;;

		*)
			echo "[!] Opción no válida."
			pausa
			;;
	esac
done
