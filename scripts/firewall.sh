#!/bin/bash

pausa(){                                                                                                                        
	echo ""
        read -p "Presione [Enter] para continuar..."
        echo ""
}

grupo_ejecucion="redesyfirewall"

usuario="${SUDO_USER:-$USER}"

if ! id -nG "$usuario" | grep -qw "$grupo_ejecucion"; then
    echo "[ERROR] Permiso denegado. Solo los miembros del grupo '$grupo_ejecucion' pueden ejecutar este script."
    pausa
    exit 0
fi

validar_firewalld(){
	if ! systemctl is-active --quiet firewalld; then
		echo "[ERROR] El servicio 'firewalld' no está en ejecución."
		pausa
		return 1
	fi
	return 0
}

mostrar_menu(){
	clear
	echo "====================================="
	echo "=        GESTIÓN DE FIREWALL        ="
	echo "====================================="
	echo ""
	echo "1) Bloquear / Desbloquear Puerto"
	echo "2) Bloquear / Desbloquear IP o Red"
	echo "3) Regla combinada (IP + Puerto)"
	echo "4) Listar reglas activas"
	echo "5) Iniciar contenedores"
	echo "6) Listar contenedores activos"
	echo "7) Salir"
	echo ""
	echo "====================================="
}

gestion_puertos(){
	clear
	echo "=============================================="
	echo "=     BLOQUEAR / DESBLOQUEAR PUERTOS         ="
	echo "=============================================="
	echo ""

	validar_firewalld || return

	echo "1) Permitir puerto (Abrir)"
	echo "2) Bloquear puerto (Cerrar)"
	read -p "Seleccione una opción [1-2]: " opcion_puerto

	read -p "Ingrese el número de puerto (ej. 80, 22): " puerto
	read -p "Ingrese el protocolo (tcp/udp): " protocolo
	protocolo=$(echo "$protocolo" | tr '[:upper:]' '[:lower:]')

	if [ -z "$puerto" ] || [ -z "$protocolo" ]; then
		echo "[ERROR] El puerto y el protocolo son obligatorios."
		pausa
		return
	fi

	echo ""
	if [ "$opcion_puerto" == "1" ]; then
		if firewall-cmd --permanent --add-port="${puerto}/${protocolo}" && firewall-cmd --reload; then
			echo "[+] Puerto ${puerto}/${protocolo} permitido con éxito."
		else
			echo "[ERROR] No se pudo agregar el puerto."
		fi
	elif [ "$opcion_puerto" == "2" ]; then
		if firewall-cmd --permanent --remove-port="${puerto}/${protocolo}" && firewall-cmd --reload; then
			echo "[+] Puerto ${puerto}/${protocolo} bloqueado con éxito."
		else
			echo "[ERROR] No se pudo remover el puerto."
		fi
	else
		echo "[!] Opción no válida."
	fi

	pausa
}

gestion_ip(){
	clear
	echo "=============================================="
	echo "=      BLOQUEAR / DESBLOQUEAR IP O RED       ="
	echo "=============================================="
	echo ""

	validar_firewalld || return

	echo "1) Bloquear IP o Red"
	echo "2) Desbloquear IP o Red"
	read -p "Seleccione una opción [1-2]: " opcion_ip

	read -p "Ingrese la IP o Red (ej. 192.168.1.50 o 192.168.1.0/24): " origen

	if [ -z "$origen" ]; then
		echo "[ERROR] Debe especificar una IP o subred."
		pausa
		return
	fi

	echo ""
	if [ "$opcion_ip" == "1" ]; then
		if firewall-cmd --permanent --add-rich-rule="rule family='ipv4' source address='${origen}' reject" && firewall-cmd --reload; then
			echo "[+] Tráfico desde ${origen} bloqueado exitosamente."
		else
			echo "[ERROR] No se pudo aplicar el bloqueo de IP."
		fi
	elif [ "$opcion_ip" == "2" ]; then
		if firewall-cmd --permanent --remove-rich-rule="rule family='ipv4' source address='${origen}' reject" && firewall-cmd --reload; then
			echo "[+] Bloqueo sobre ${origen} eliminado con éxito."
		else
			echo "[ERROR] No se pudo remover el bloqueo."
		fi
	else
		echo "[!] Opción no válida."
	fi

	pausa
}

combinacion_ip_puerto(){
	clear
	echo "=============================================="
	echo "=     COMBINACIÓN IP + PUERTO (RICH RULE)    ="
	echo "=============================================="
	echo ""

	validar_firewalld || return

	echo "1) Permitir puerto a una IP/Red específica"
	echo "2) Bloquear puerto a una IP/Red específica"
	read -p "Seleccione una opción [1-2]: " opcion_comb

	read -p "Ingrese la IP o Red origen (ej. 192.168.1.10): " origen
	read -p "Ingrese el puerto destino (ej. 22, 8080): " puerto
	read -p "Ingrese el protocolo (tcp/udp): " protocolo
	protocolo=$(echo "$protocolo" | tr '[:upper:]' '[:lower:]')

	if [ -z "$origen" ] || [ -z "$puerto" ] || [ -z "$protocolo" ]; then
		echo "[ERROR] Todos los campos son obligatorios."
		pausa
		return
	fi

	if [ "$opcion_comb" == "1" ]; then
		accion="accept"
	elif [ "$opcion_comb" == "2" ]; then
		accion="reject"
	else
		echo "[!] Opción no válida."
		pausa
		return
	fi

	regla="rule family='ipv4' source address='${origen}' port port='${puerto}' protocol='${protocolo}' ${accion}"

	echo ""
	echo "Aplicando regla: $regla"

	if firewall-cmd --permanent --add-rich-rule="$regla" && firewall-cmd --reload; then
		echo "[+] Regla combinada aplicada correctamente."
	else
		echo "[ERROR] Falló la creación de la regla combinada."
	fi

	pausa
}

listar_reglas(){
	clear
	echo "=============================================="
	echo "=            REGLAS ACTIVAS                  ="
	echo "=============================================="
	echo ""

	validar_firewalld || return

	firewall-cmd --list-all

	echo ""
	echo "=============================================="
	pausa
}

validar_docker(){
	ruta_docker=$(command -v docker)

	if [ -z "$ruta_docker" ]; then
		echo "[ERROR] Docker no está instalado"
	pausa
	return 1
	fi
}


iniciar_contenedores(){
	clear
	echo "=============================================="
	echo "=            INICIAR CONTENEDORES            ="
	echo "=============================================="
	echo ""

	validar_docker || return
	detenidos=$(docker ps -a --filter "status=exited" --filter "status=created" --format "{{.Names}}")

	if [ -z "$detenidos" ]; then
		echo "No hay contenedores para iniciar"
		pausa
		return
	fi

	echo "= Contenedores detenidos ="
	echo ""
	docker ps -a --filter "status=exited" --filter "status=created" --format "table {{.Names}}\t{{.Image}}\t{{.Status}}"
	echo ""

	read -p "Nombre del contenedor a iniciar : " contenedor

	if [ -z "$contenedor" ]; then
		echo "Debe ingresar un nombre"
		pausa
		return
	fi

	if ! echo "$detenidos" | grep -qx "$contenedor"; then
		echo "[ERROR] '$contenedor' no está en la liste de contenedores detenidos"
		pausa
		return
	fi

	if docker start "$contenedor"; then
		echo "Contenedor '$contenedor' iniciado"
	else
		echo "[ERROR] No se pudo iniciar el contenedor"
	fi
	pausa
}

listar_contenedores(){
	clear
	echo "=============================================="
	echo "=           CONTENEDORES ACTIVOS             ="
	echo "=============================================="
	echo ""

	validar_docker || return
	activos=$(docker ps -q)

	if [ -z "$activos" ]; then
		echo "No hay contenedores en ejecución"
	else
		docker ps --format "table {{.ID}}\t{{.Names}}\t{{.Image}}\t{{.Status}}\t{{.Ports}}"
	fi

	echo ""
	echo "=============================================="
	pausa
}

opcion=999

while [ $opcion != "7" ]; do
	mostrar_menu
	read -p "Seleccione una opción: " opcion
	echo ""
	case $opcion in

		1)
			gestion_puertos
			;;

		2)
			gestion_ip
			;;

		3)
			combinacion_ip_puerto
			;;

		4)
			listar_reglas
			;;

		5)
			iniciar_contenedores
			;;

		6)
			listar_contenedores
			;;

		7)
			echo "Saliendo..."
			;;


		*)
			echo "[!] Opción no válida."
			pausa
			;;
	esac
done
