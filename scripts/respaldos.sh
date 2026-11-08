#!/bin/bash

pausa(){
	echo ""
        read -p "Presione [Enter] para continuar..."
        echo ""
}

grupo_ejecucion="respaldos"

usuario="${SUDO_USER:-$USER}"

if ! id -nG "$usuario" | grep -qw "$grupo_ejecucion"; then
    echo "[ERROR] Permiso denegado. Solo los miembros del grupo '$grupo_ejecucion' pueden ejecutar este script."
    pausa
    exit 0
fi

ruta_respaldos="/var/backups"

mostrar_menu(){
	clear
	echo "====================================="
	echo "=        GESTIÓN DE RESPALDOS       ="
	echo "====================================="
	echo ""
	echo "1) Crear respaldo local (.tar.gz)"
	echo "2) Enviar respaldo a servidor remoto (SCP)"
	echo "3) Programar respaldo automático (Cron)"
	echo "4) Listar respaldos existentes"
	echo "5) Crear respaldo de Volumen Docker"
	echo "6) Salir"
	echo ""
	echo "====================================="
}

verificar_directorio(){
	if [ ! -d "$ruta_respaldos" ]; then
		mkdir -p "$ruta_respaldos"
	fi
}

respaldo_local(){
	clear
	echo "=============================================="
	echo "=            CREAR RESPALDO LOCAL            ="
	echo "=============================================="
	echo ""

	read -p "Ingrese la ruta absoluta del directorio a respaldar: " ruta_origen

	if [ -z "$ruta_origen" ] || [ ! -d "$ruta_origen" ]; then
		echo "[ERROR] La ruta especificada no existe o no es válida."
		pausa
		return
	fi

	verificar_directorio

	# Genera un nombre único con timestamp (Año-Mes-Día_HoraMinuto)
	fecha_hora=$(date +"%Y%m%d_%H%M%S")
	nombre_base=$(basename "$ruta_origen")
	archivo_destino="$ruta_respaldos/backup_${nombre_base}_${fecha_hora}.tar.gz"

	echo ""
	echo "Comprimiendo '$ruta_origen'..."

	if tar -czf "$archivo_destino" "$ruta_origen" --warning=none; then #--warning=none silencia los errores de tar
		echo "[+] Respaldo creado con éxito en:"
		echo "    $archivo_destino"
	else
		echo "[ERROR] Falló la creación del respaldo comprimido."
	fi

	pausa
}

respaldo_remoto(){
	clear
	echo "=============================================="
	echo "=           ENVIAR RESPALDO REMOTO           ="
	echo "=============================================="
	echo ""

	verificar_directorio

	archivos=$(ls "$ruta_respaldos"/*.tar.gz 2>/dev/null) #2>/dev/null redirige los errores al directorio null donde se eliminan para que el usuario no vea ningun error.

	if [ -z "$archivos" ]; then
		echo "[!] No hay respaldos locales disponibles en $ruta_respaldos."
		echo "    Cree un respaldo local primero."
		pausa
		return
	fi

	echo "Respaldos disponibles:"
	echo "----------------------------------------------"
	ls -1 "$ruta_respaldos"/*.tar.gz
	echo "----------------------------------------------"
	echo ""

	read -p "Nombre del archivo a enviar (solo el nombre del archivo): " archivo_sel
	ruta_completa="$ruta_respaldos/$archivo_sel"

	if [ ! -f "$ruta_completa" ]; then
		echo "[ERROR] El archivo seleccionado no existe."
		pausa
		return
	fi

	read -p "Usuario remoto (ej. root): " usuario_rem
	read -p "IP o Host remoto (ej. 192.168.2.30): " ip_rem
	read -p "Ruta de destino en el servidor remoto (ej. /tmp): " ruta_rem

	if [ -z "$usuario_rem" ] || [ -z "$ip_rem" ] || [ -z "$ruta_rem" ]; then
		echo "[!] Todos los campos son obligatorios."
		pausa
		return
	fi

	echo ""
	echo "Enviando '$archivo_sel' a $usuario_rem@$ip_rem:$ruta_rem..."

	if scp "$ruta_completa" "$usuario_rem@$ip_rem:$ruta_rem"; then
		echo "[+] Transferencia remota completada correctamente."
	else
		echo "[ERROR] Ocurrió un fallo en la transferencia por SCP."
	fi

	pausa
}

programar_cron(){
	clear
	echo "=============================================="
	echo "=         PROGRAMAR RESPALDO (CRON)          ="
	echo "=============================================="
	echo ""

	read -p "Ruta del directorio a respaldar automáticamente: " ruta_origen

	if [ -z "$ruta_origen" ] || [ ! -d "$ruta_origen" ]; then
		echo "[ERROR] La ruta no existe."
		pausa
		return
	fi

	echo ""
	echo "Seleccione la frecuencia:"
	echo "1) Diariamente"
	echo "2) Cada hora"
	echo "3) Cada domingo"
	read -p "Opción [1-3]: " freq

	verificar_directorio

	cmd="tar -czf $ruta_respaldos/auto_\$(basename $ruta_origen)_\$(date +\%Y\%m\%d_\%H\%M\%S).tar.gz $ruta_origen"

	case $freq in
		1) tiempo_cron="0 0 * * *" ;;
		2) tiempo_cron="0 * * * *" ;;
		3) tiempo_cron="0 0 * * 0" ;;
		*) echo "[!] Opción no válida."; pausa; return ;;
	esac

	(crontab -l 2>/dev/null; echo "$tiempo_cron $cmd") | crontab -

	echo ""
	echo "[+] Tarea programada exitosamente en Cron:"
	echo "    $tiempo_cron $cmd"

	pausa
}

listar_respaldos(){
	clear
	echo "=============================================="
	echo "=            RESPALDOS EXISTENTES            ="
	echo "=============================================="
	echo ""

	if [ -d "$ruta_respaldos" ] && [ "$(ls -A "$ruta_respaldos")" ]; then
		ls -lh "$ruta_respaldos"
	else
		echo "[!] No se encontraron respaldos en $ruta_respaldos."
	fi

	echo ""
	echo "=============================================="
	pausa
}

respaldo_volumen_docker(){
	clear
	echo "=============================================="
	echo "=     CREAR RESPALDO DE VOLUMEN DOCKER       ="
	echo "=============================================="
	echo ""
	
	echo "Volúmenes de Docker disponibles:"
	docker volume ls --format "{{.Name}}"
	echo "----------------------------------------------"
	echo ""

	read -p "Ingrese el nombre del volumen a respaldar: " nombre_volumen

	if [ -z "$nombre_volumen" ]; then
		echo "[ERROR] Debe especificar un nombre."
		pausa
		return
	fi

	verificar_directorio

	fecha_hora=$(date +"%Y%m%d_%H%M%S")
	archivo_destino="$ruta_respaldos/backup_vol_${nombre_volumen}_${fecha_hora}.tar.gz"
	nombre_archivo=$(basename "$archivo_destino")

	echo ""
	echo "Comprimiendo el volumen '$nombre_volumen'..."

	# Usa un contenedor temporal de Alpine para montar el volumen y el directorio de respaldos
	if docker run --rm \
		-v "$nombre_volumen":/volumen_origen:ro \
		-v "$ruta_respaldos":/directorio_destino \
		alpine tar -czf "/directorio_destino/$nombre_archivo" -C /volumen_origen . ; then
		
		echo "[+] Respaldo de volumen creado con éxito en:"
		echo "    $archivo_destino"
	else
		echo "[ERROR] Falló la creación del respaldo de Docker."
		echo "Asegúrese de tener permisos de Docker y que el volumen exista."
	fi

	pausa
}

opcion=999

while [ $opcion != "6" ]; do
	mostrar_menu
	read -p "Seleccione una opción: " opcion
	echo ""
	case $opcion in

		1)
			respaldo_local
			;;

		2)
			respaldo_remoto
			;;

		3)
			programar_cron
			;;

		4)
			listar_respaldos
			;;

		5)
			respaldo_volumen_docker
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


