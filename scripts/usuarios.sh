#!/bin/bash
mostrar_menu(){
	 clear
	 echo "====================================="
	 echo "=        GESTIÓN DE USUARIOS        ="
	 echo "====================================="
	 echo ""
         echo "1) Crear usuario"
         echo "2) Eliminar usuario"
         echo "3) Agregar usuario a un grupo"
         echo "4) Listar usuarios"
         echo "5) Cambiar contraseña de un usuario"
         echo "6) Salir"
	 echo ""
         echo "====================================="
}

pausa(){
        echo ""
        read -p "Presione [Enter] para continuar..."
        echo ""
}

crear_usuario(){
	
	echo "=============================================="
        echo "=               CREAR  USUARIO               ="
        echo "=============================================="
        echo ""

	read -p "Nombre de usuario: " usuario
	
	if [ -z "$usuario" ]; then
		echo "[!] Debe ingresar un nombre válido."
        	return
	fi

	usuario_existe=$(grep -w "^$usuario" /etc/passwd)
	
	if [ -n "$usuario_existe" ]; then
        	echo "[ERROR] Ese usuario ya existe."
        	return
    	fi
	
	if useradd -m "$usuario"; then
        	passwd "$usuario"
        	echo "[+] Usuario $usuario creado correctamente."
    	else
        	echo "[ERROR] No se pudo crear el usuario."
    	fi
}

eliminar_usuario(){
	
	echo "=============================================="
        echo "=             ELIMINAR  USUARIO              ="
        echo "=============================================="
        echo ""

	read -p "Nombre de usuario: " usuario

	if [ -z "$usuario" ]; then
                echo "[!] Debe ingresar un nombre válido."
                return
        fi

        usuario_existe=$(grep -w "^$usuario" /etc/passwd)

        if [ -z "$usuario_existe" ]; then
                echo "[ERROR] Ese usuario no existe."
                return
        fi

        userdel -r "$usuario"
        echo "[-] Usuario $usuario eliminado correctamente."
}

agregar_usuario_grupo(){
	
	echo "=============================================="
        echo "=         AGREGAR USUARIO A UN GRUPO         ="
        echo "=============================================="
	echo ""

	###Ingreso y verificación del usuario
	
	read -p "Nombre de usuario: " usuario

        if [ -z "$usuario" ]; then
                echo "[!] Debe ingresar un nombre válido."
                return
        fi

        usuario_existe=$(grep -w "^$usuario" /etc/passwd)

        if [ -z "$usuario_existe" ]; then
                echo "[ERROR] Ese usuario no existe."
                return
        fi


	###Ingreso y verificación del grupo
	
	read -p "Nombre del grupo secundario: " grupo
	
	if [ -z "$grupo" ]; then
                echo "[!] Debe ingresar un nombre válido."
                return
        fi

	grupo_existe=$(grep -w "^$grupo" /etc/group)
	
	if [ -z "$grupo_existe" ]; then
                echo "[ERROR] Ese grupo no existe."
                return
        fi
	
	usermod -aG "$grupo" "$usuario"
    	echo "[+] Usuario '$usuario' agregado correctamente al grupo '$grupo'."
}

listar_usuarios(){
	
	clear
	echo "=============================================="
        echo "=              LISTA DE USUARIOS             ="
        echo "=============================================="
	echo ""

	awk -F: '$3 >= 1000 && $1 != "nobody" { print $1 }' /etc/passwd
	
	echo ""
	echo "=============================================="

	pausa
}

cambiar_contrasena(){
	
	echo "=============================================="
        echo "=            CAMBIAR  CONTRASEÑA             ="
        echo "=============================================="
        echo ""

	read -p "Nombre de usuario: " usuario

        if [ -z "$usuario" ]; then
                echo "[!] Debe ingresar un nombre válido."
                return
        fi

        usuario_existe=$(grep -w "^$usuario" /etc/passwd)

        if [ -z "$usuario_existe" ]; then
                echo "[ERROR] Ese usuario no existe."
                return
        fi

	passwd "$usuario"
}

opcion=999

while [ $opcion != "6" ]; do
	mostrar_menu
	read -p "Seleccione una opción: " opcion
	echo ""
	case $opcion in
		
		1)
			crear_usuario
			pausa
			;;

		2)
			eliminar_usuario
			pausa
			;;

		3)
			agregar_usuario_grupo
			pausa
			;;

		4)
			listar_usuarios
			;;

		5)
			cambiar_contrasena
			pausa
			;;

		6)
			echo "Saliendo..."
			;;
		
		*)
			echo "[!] Opción no válida."
			pausa
			;;
	esac
done
