<template>
    <div>
        <div class="card" v-for="(item, index) in items" :key="index">
        <img :src="'/img/card-company.svg'" class="card-img">
        <div class="card-img-overlay">
            <h4 class="card-title company_name">{{ item.name }}</h4>
            <div class="row company_contant">
                <div class="col-md-6">
                    <h5 class="mt-2">Контакты</h5> 
                    <ul>
                        <li class="contant_item"><i class="fas fa-map-marker-alt mr-2"></i> {{ item.address }}</li>
                        <li class="contant_item"><i class="fas fa-phone-alt mr-1"></i> {{ item.phones }}</li>
                        <li class="contant_item"><i class="fas fa-envelope mr-1"></i> <a href="#">{{ item.emails }}</a></li>
                        <li class="contant_item"><i class="fas fa-at mr-1"></i> <a href="#">{{ item.website }}</a></li>
                    </ul> 
                </div>
                <div class="col-md-6">
                    <h5 class="mt-2">Менеджеры компании</h5>
                    <ul>
                        <li class="company_contant_item">O.Sergeev@molkom-adyg.ru</li>
                        <li class="company_contant_item">T.Nikolaev@molkom-adyg.ru</li>
                    </ul>
                    <h5 class="mt-4">Контактное лицо</h5>
                    <ul>
                        <li>Иванов Иван, менеджер</li>
                    </ul>
                </div>
            </div>
            <div class="company_show text-right">
                <span v-b-toggle="'collapse-'+index"><i class="fas fa-info-circle mr-2"></i>Подробная информация</span><br>
            </div>
        </div>
        <b-collapse :id="'collapse-'+index" class="company_collapse ml-5 mr-5">
            <h5 class="mt-2 mb-3">Основная информация</h5> 
            <table class="table company_info_table">
            <tbody>
                <tr>
                    <td class="company_info_item">Тип:</td>
                    <td>Экспортер</td>
                </tr>
                <tr>
                    <td class="company_info_item">Полное название компании</td>
                    <td>{{item.name}}</td>
                </tr>
                <tr>
                    <td class="company_info_item">Юридический адрес</td>
                    <td>{{item.address}}</td>
                </tr>
                <tr>
                    <td class="company_info_item">ФИО директора</td>
                    <td>{{item.director}}</td>
                </tr>
                <tr>
                    <td class="company_info_item">В лице</td>
                    <td>{{item.type_director}}</td>
                </tr>
                <tr>
                    <td class="company_info_item">ИНН</td>
                    <td>{{item.inn}}</td>
                </tr>
                <tr>
                    <td class="company_info_item">ОГРН</td>
                    <td>{{item.ogrn}}</td>
                </tr>
                <tr>
                    <td class="company_info_item">КПП</td>
                    <td>{{item.kpp}}</td>
                </tr>
                <tr>
                    <td class="company_info_item">БИК</td>
                    <td>{{item.bic}}</td>
                </tr>
                <tr>
                    <td class="company_info_item">Название банка</td>
                    <td>{{item.bank_name}}</td>
                </tr>
                <tr>
                    <td class="company_info_item">Корр. счет</td>
                    <td>{{item.correspondent_account}}</td>
                </tr>
                <tr>
                    <td class="company_info_item">Расч. счет</td>
                    <td>{{item.payment_account}}</td>
                </tr>                    
            </tbody>
            </table>
        </b-collapse>
        </div>
    </div>
</template>

<script>
import { API_USERS } from '../constants'

export default {
    data() {
        return {
            items: '',
            user: auth.user,
        }
    },
    mounted() {
        Event.$on('userLoggedIn', () => {
            this.user = auth.user;
        });
        this.getUserCompany()    
    },
    methods: {
        getUserCompany(){
            api.call('get', API_USERS+`/${this.user.id}`).then(({data}) => {
                this.items = data.companies
            })
        }
    }
}
</script>