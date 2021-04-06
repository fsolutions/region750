// Различные методы для работы с обращениями
export const orderMethods = {
    methods: {
        updateOneOrderByIndex(index) {
            let id = this.items.data[index].id
            api.call("get", `${this.tableApiUrl}/${id}`).then(({data}) => {
                this.items.data[index] = Object.assign(this.items.data[index], data)
            }).finally(() => {
            })
        },
        closeOrderByIndex(index) {
            let id = this.items.data[index].id
            let status_id = 362                     // Обращение закрыт
            let radioButtonList = this.$store.getters.GROUPED_REFERENCE_PROPERTIES[36]
            let updateFields = {
                reference_status_id: status_id,
                reference_close_reason_id: 368,
                close_comment: ''
            }

            let radioButtonHTML = ''
            radioButtonList.forEach((property, index) => {
                let checked = ''
                if (index == 0) { checked = 'checked'}
                radioButtonHTML += `<div class="custom-control custom-radio">
                    <input type="radio" id="closingOrderReason${index}" ${checked} name="closingOrderReason" class="custom-control-input" value="${property.id}">
                    <label class="custom-control-label" for="closingOrderReason${index}">${property.name}</label>
                </div>`
            })

            radioButtonHTML = `<div class="text-left">
                                    ${radioButtonHTML}
                                    <div class="form-group mt-4">
                                        <label for="closingOrderReasonComment">Комментарий</label>
                                        <textarea class="form-control" id="closingOrderReasonComment" rows="3"></textarea>
                                    </div>                                    
                                </div>`

            // we ask for stay comment
            this.$swal.fire({
                title: 'Укажите причину',
                html: radioButtonHTML,
                focusConfirm: false,
                showCancelButton: true,
                cancelButtonText: 'Передумал(а)',
                confirmButtonText: 'Закрыть заявку',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    let radios = document.getElementsByName('closingOrderReason')
                    radios.forEach((radio, index) => {
                        if (radio.type == "radio" && radio.checked) {
                            updateFields.reference_close_reason_id = Number(radio.value)
                        }
                    })
                    updateFields.close_comment = document.getElementById("closingOrderReasonComment").value
                    return this.updateAnyOrder(id, updateFields, index)
                },
                allowOutsideClick: () => !this.$swal.isLoading()
            }).then((result) => {
            }).finally(() => {
            })
        },
        closeOrderFinallyByIndex(index) {
            let id = this.items.data[index].id
            let status_id = 361                     // Обращение завершен
            let updateFields = {
                reference_status_id: status_id,
                comment: this.items.data[index].comment
            }

            let successClosingOrderHTML = `<div class="text-left">
                                            <div class="form-group mt-4">
                                                <label for="successClosingOrderComment">Комментарий (пару слов о обращениее)</label>
                                                <textarea class="form-control" id="successClosingOrderComment" rows="3">${updateFields.comment}</textarea>
                                            </div>                                    
                                        </div>`

            // we ask for stay comment
            this.$swal.fire({
                title: 'Отлично! Завершаем поставку?',
                html: successClosingOrderHTML,
                focusConfirm: false,
                showCancelButton: true,
                cancelButtonText: 'Передумал(а)',
                confirmButtonText: 'Завершаем',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    updateFields.comment = document.getElementById("successClosingOrderComment").value
                    return this.updateAnyOrder(id, updateFields, index)
                },
                allowOutsideClick: () => !this.$swal.isLoading()
            }).then((result) => {
            }).finally(() => {
            })
        },
        updateAnyOrder(id, data, index = -1) {
            return api.call("put", `${this.tableApiUrl}/${id}`, data).then(({data}) => {
                if (index != -1) {
                    Object.assign(this.items.data[index], data)
                    Object.assign(this.detailedItem, data)
                    this.items.data[index].loading = false
                }
                this.validationErrors = ''
                this.makeToast('status-changed-success')

                return true
            }).catch((response) => {
                if (response.status == 422){
                    this.validationErrors = response.data.error
                }
            })
        }
    }
}
