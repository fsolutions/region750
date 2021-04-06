// Замена запятых на точки на лету
export const changeCommasOnDots = {
    methods: {
        changeCommasOnDots: function(event) {
            event.target.value = event.target.value.replace(",", ".");
        }
    }
}

// Обрезка значений в инпутах на лету
export const maxCharsLimit = {
    methods: {
        maxCharsLimit(maxLength, valueId, objectId = 'editedItem') {
            if (this[objectId][valueId].length >= maxLength) {
                this[objectId][valueId] = this[objectId][valueId].substring(0, maxLength);
            }
        }
    }
}



