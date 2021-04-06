// Дефольные данные в data которые должны быть почти на всех страницах
export const defaultDataItems = {
    components: {
    },
    data() {
        return {
            items: {
                data: [],
                headers: [],
                actionAllows: [],
                current_page: 1,
                per_page: 25,
                total: 0,
            },
            dataOverlay: true
        }
    },
    mounted() {
    },
    methods: {
        makeToast(variant = null, title = 'Оповещение') {
            let message = 'Успешно! Сохранено.'

            switch (variant) {
                case 'danger':
                    message = 'Успешное удаление'
                    break;
                case 'success':
                    message = 'Успешно сохранено'
                    break;
                case 'sended-success':
                    message = 'Успешно отправлено'
                    variant = 'success'
                    break;
                case 'status-changed-success':
                    message = 'Статус успешно изменен'
                    variant = 'success'
                    break;
                default:
                    variant = 'success'
            }

            this.$bvToast.toast(message, {
                title: title,
                variant: variant,
                solid: true
            })
        },
        makeErrorToast(variant = null) {
            this.$bvToast.toast('Ошибка загрузки документа, попробуйте снова', {
                title: `Предупреждение`,
                variant: variant,
                solid: true
            })
        },
    }
}
