<template>
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">Users Component</div>

                    <div class="panel-body">
                        <ul class="list-group">
                            <li v-for="user in users" class="list-group-item">
                                <p>{{user.name}} {{user.last_name}}</p>
                            </li>
                        </ul>
                        <p v-model="activeUsers">{{activeUsers}}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['user_id'],
        data(){
            return  {
                users: [],
            }
        },
        computed: {
      
        },
        mounted() {
            this.loadUsers();
        },
        methods: {
            loadUsers(){
                console.log('users')
                axios.get('/loadusers').then((response)=> {
                    console.log(response.data)
                    this.users = response.data
                });
            },
        },
    }
</script>
