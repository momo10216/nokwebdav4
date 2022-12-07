#!/bin/sh

SAVEIFS=$IFS
IFS=""

# Functions
replace_version () {
    # Arguments: filename, currentversion, newversion
    cat "${1}" | sed "s/<version>${2}<\/version>/<version>${3}<\/version>/g" > "${1}.new"
    mv -f "${1}.new" "${1}"
}

get_manifest () {
    # Arguments: element
    if [ -r "${1}.xml" ]; then
    	MANIFEST_FILE="${1}.xml"
    	return
    else
	for XMLFILE in "*.xml"; do
	    MANIFEST_FILE="${XMLFILE}"
	    return
	done
    fi
}

get_folders_from_file () {
    ZIP_ELEMENTS=""
    while read ZIP_ELEMENT; do
        if [ -z ${ZIP_ELEMENTS} ]; then
            ZIP_ELEMENTS="\"${ZIP_ELEMENT}\""
        else
            ZIP_ELEMENTS="${ZIP_ELEMENTS} \"${ZIP_ELEMENT}\""
        fi
    done < ${1}
}

get_folders_from_package_list () {
    TMPFILE="$(mktemp)"
    grep -ioP 'folder="[^\"]+"' "${1}" | sed 's/folder=//g' | sed 's/"//g' | sort | uniq > ${TMPFILE}
    get_folders_from_file "${TMPFILE}" 
    rm -f "${TMPFILE}"
}

# Get root path
ROOT_PATH="."
if [ -r ../pkg_nokwebdav.xml ]; then ROOT_PATH=".."; fi

# Enter version
VERSION_CURRENT=$(grep "<version>" ${ROOT_PATH}/pkg_nokwebdav.xml | sed 's/\s//g' | sed 's/<[\/]*version>//g')
echo -n "Current version '${VERSION_CURRENT}'. New version (leave empty for not changing): "
read VERSION_NEW
if [ -n "${VERSION_NEW}" -a "${VERSION_CURRENT}" != "${VERSION_NEW}" ]; then
    replace_version "${ROOT_PATH}/pkg_nokwebdav.xml" "${VERSION_CURRENT}" "${VERSION_NEW}"
    for DIR in ${ROOT_PATH}/src/*; do
        for XMLFILE in ${DIR}/*.xml; do
            if [ -w "${XMLFILE}" ]; then
                replace_version "${XMLFILE}" "${VERSION_CURRENT}" "${VERSION_NEW}"
            fi
        done
    done
fi

# Create element zips
CURRENT_DIR="$(pwd)"
cd "${ROOT_PATH}/src/"
for DIR in *; do
    echo "Create element package '${DIR}'"
    cd "${CURRENT_DIR}"
    cd "${ROOT_PATH}/src/${DIR}"
    ZIPFILE="../../packages/${DIR}.zip"
    if [ -r "${ZIPFILE}" ]; then rm -f "${ZIPFILE}"; fi
    get_folders_from_file "package_list.txt"
    CMD="zip -rq \"${ZIPFILE}\" ${ZIP_ELEMENTS}"
    #echo ${CMD}
    eval "${CMD}"
done
cd "${CURRENT_DIR}"

# Create package zip
echo "Create package 'pkg_nokwebdav'"
cd "${ROOT_PATH}"
get_folders_from_file "package_list.txt"
ZIPFILE="pkg_nokwebdav-${VERSION_NEW}.zip"
CMD="zip -rq \"${ZIPFILE}\" ${ZIP_ELEMENTS}"
#echo ${CMD}
eval "${CMD}"

IFS=$SAVEIFS

