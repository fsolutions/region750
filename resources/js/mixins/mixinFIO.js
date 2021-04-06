// Разбиение или слияние ФИО на поля
// Необходимо fieldFIO в виде поля в объекте редактирования
export const mixinFIO = {
    data() {
        return {
            firstName: '',     // Имя
            lastName: '',      // Фамилия
            surName: ''        // Отчество
        }
    },
    methods: {
        splitFIO() {
            if (this.editedItem[this.fieldFIO]) {
                let arr = this.editedItem[this.fieldFIO].split(' ')
                this.lastName = arr[0]
                this.firstName = arr[1]
                this.surName = arr[2] ? arr[2] : ''
            }
        },
        setFIO() {
            if(this.lastName && this.firstName && this.surName) {
                this.editedItem[this.fieldFIO] = `${this.lastName} ${this.firstName} ${this.surName}`
            } else if(this.lastName && this.firstName) {
                this.editedItem[this.fieldFIO] = `${this.lastName} ${this.firstName}`
            }

        }
    }
}
