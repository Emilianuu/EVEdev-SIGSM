#!/bin/bash

if [ "$(whoami)" != "root" ]; then
    echo "[ERROR] Debes ser usuario 'root' o usar 'sudo' para ejecutar este script."
    exit 1
fi

mostrar_menu(){
	clear
	echo "================================="
	echo "=    PANEL DE ADMINISTRACIÓN    ="
	echo "================================="
	echo ""
	echo "1) Gestión de usuarios"
	echo "2) Gestión de grupos"
	echo "3) Gestión de respaldos"
	echo "4) Gestión de Firewall"
	echo "5) Gestión de Logs del Sistema"
	echo "6) Gestión de redes"
	echo "7) Salir"
	echo ""
	echo "================================="
}

pausa(){
	echo ""
	read -p "Presione [Enter] para continuar..."
	echo ""
}

opcion=999

while [ $opcion != "7" ]; do
	mostrar_menu
	read -p "Seleccione una opción [1-7]: " opcion
	echo ""
	case $opcion in

		1) 
			if [ -f "./scripts/usuarios.sh" ]; then
            			bash ./scripts/usuarios.sh
        		else
            			echo "[ERROR] El script de usuarios no existe."
				pausa
        		fi
        		;;

		2)
                        if [ -f "./scripts/grupos.sh" ]; then
                                bash ./scripts/grupos.sh
                        else
                                echo "[ERROR] El script de grupos no existe."
				pausa
                        fi
                        ;;

		3)
                        if [ -f "./scripts/respaldos.sh" ]; then
                                bash ./scripts/respaldos.sh
                        else
                                echo "[ERROR] El script de respaldos no existe."
				pausa
                        fi
                        ;;

		4)
                        if [ -f "./scripts/firewall.sh" ]; then
                                bash ./scripts/firewall.sh
                        else
                                echo "[ERROR] El script de firewall no existe."
				pausa
                        fi
			;;

		5)
                        if [ -f "./scripts/logs.sh" ]; then
                                bash ./scripts/logs.sh
                        else
                                echo "[ERROR] El script de logs no existe."
				pausa
                        fi
                        ;;

		6)
			# Esta parte del codigo verifica que el usuario sea del grupo redes y firewall para ejecutarlo
			grupo_ejecucion="redesyfirewall"

			usuario="${SUDO_USER:-$USER}"

			if ! id -nG "$usuario" | grep -qw "$grupo_ejecucion"; then
    				echo "[ERROR] Permiso denegado. Solo los miembros del grupo '$grupo_ejecucion' pueden ejecutar este script."
    				pausa
			else
				nmtui
			fi
			;;

		7)
			echo "Saliendo..."
			echo ""
			;;
			
		*)
			echo "[!] Opción no valida."
			pausa
			;;
	esac
done	
