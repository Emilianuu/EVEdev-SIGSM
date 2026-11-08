#!/bin/bash
mostrar_menu(){
         clear
         echo "====================================="
         echo "=         GESTIÓN DE GRUPOS         ="
         echo "====================================="
         echo ""
         echo "1) Crear grupo"
         echo "2) Eliminar grupo"
         echo "3) Listar grupos"
         echo "4) Salir"
         echo ""
         echo "====================================="
}

pausa(){
        echo ""
        read -p "Presione [Enter] para continuar..."
        echo ""
}

crear_grupo()
{
	echo "=============================================="
        echo "=                CREAR  GRUPO                ="
        echo "=============================================="
        echo ""

        read -p "Nombre del grupo: " grupo

        if [ -z "$grupo" ]; then
                echo "[!] Debe ingresar un nombre válido."
                return
        fi

        grupo_existe=$(grep -w "^$grupo" /etc/group)

        if [ -n "$grupo_existe" ]; then
                echo "[ERROR] Ese grupo ya existe."
                return
        fi

        if groupadd "$grupo"; then
                echo "[+] Grupo $grupo creado correctamente."
        else
                echo "[ERROR] No se pudo crear el grupo."
        fi
}

eliminar_grupo(){

        echo "=============================================="
        echo "=              ELIMINAR  GRUPO               ="
        echo "=============================================="
        echo ""

        read -p "Nombre del grupo: " grupo

        if [ -z "$grupo" ]; then
                echo "[!] Debe ingresar un nombre válido."
                return
        fi

        grupo_existe=$(grep -w "^$grupo" /etc/group)

        if [ -z "$grupo_existe" ]; then
                echo "[ERROR] Ese grupo no existe."
                return
        fi

        if groupdel "$grupo"; then
        	echo "[-] Grupo '$grupo' eliminado correctamente."
    	else
        	echo "[ERROR] No se pudo eliminar el grupo (asegúrese de que no sea el grupo primario de ningún usuario)."
    	fi
}

listar_grupos(){

	clear
        echo "=============================================="
        echo "=               LISTA DE GRUPOS              ="
        echo "=============================================="
        echo ""

        awk -F: '$3 >= 1000 && $1 != "nogroup" && $1 != "nobody" { print $1 }' /etc/group

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
                        crear_grupo
                        pausa
                        ;;

                2)
                        eliminar_grupo
                        pausa
                        ;;

                3)
                        listar_grupos
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
