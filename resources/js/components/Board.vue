<template>
    <div>
        <modal name="edit_card" :clickToClose="false">
            <div class="pa-2">
                <h2 class="mt-0">Update Card</h2>
                <input type="text" v-model="card_title" /><br/><br/>
                <textarea v-model="card_description" cols="30" rows="5"></textarea><br/><br/>

                <button v-on:click="closeModal()" class="button is-white">Close</button>
                <button v-on:click="updateCard()" class="button is-black">Update</button>
            </div>
        </modal>

        <CreateColumn :columns="columns" v-on:load-board="init" />
        <hr />
        <div class="error">{{error}}</div>

        <section class="section">
            <div class="container">
                <h2>
                    Task Board
                    <button class="pull-right" v-on:click="exportDB()">Export DB</button>
                </h2>
                <div class=columns>
                    <div class="column status-1" v-for="column in columns" v-bind:key="column.id">
                        <div class="tags has-addons">
                            <span class="tags-tag"><em>NAME</em>: {{ column.name }}</span>
                            <span class="tags-tag is-dark">{{column.cards.length}}</span>

                            <span class="tags-tag is-danger link ml-2" v-on:click="remove(column.id)">Remove</span>
                        </div>

                        <div class="card" v-for="task in column.cards" v-bind:key="task.id">
                            <div class="card-header">
                                <div class="card-header-title is-centered">{{ task.title }} <button v-on:click="editCard(task.id)" class="ml-2 is-light">Edit</button></div>
                            </div>
                            <div class="card-content">{{ task.description }}</div>
                            
                            <div class="card-footer">
                                <a class="card-footer-item link" @click="relocate(column.id, task.id, 1)">Left</a>
                                <a class="card-footer-item link" @click="relocate(column.id, task.id, 2)">Up</a>
                                <a class="card-footer-item link" @click="relocate(column.id, task.id, 3)">Down</a>
                                <a class="card-footer-item link" @click="relocate(column.id, task.id, 4)">Right</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<script>
import CreateColumn from './CreateColumn.vue';

export default {
    components: {
		CreateColumn
	},
    data() {
        return {
            columns: [],
            tasks: [
                { name: 'task 1', status: 1, description: 'Test Description 1' },
                { name: 'task 2', status: 1, description: 'Test Description 2' },
                { name: 'task 3', status: 2, description: 'Test Description 3' },
                { name: 'task 4', status: 3, description: 'Test Description 4' }
            ],
            error: '',
            edit_card_id: 0,
            card_title: '',
            card_description: '',
        }
    },
    created() {
        this.init();
    },
    computed: {
        /*tasksOpen: function () {
        return filters.open(this.tasks)
        },
        tasksDoing: function () {
        return filters.doing(this.tasks)
        },
        tasksClosed: function () {
        return filters.closed(this.tasks)
        }*/
    },
    methods: {
        init() {
            axios.get('/api/get-info')
            .then((response) => {
                this.columns = response.data.message;
            }).catch((error) => {
                console.log(error);
            })
        },
        remove(id) {
            this.error = '';
            if(confirm('Are you sure. You want to Delete?')) {
                axios.delete('/api/delete-column/'+id)
                .then(() => {
                    this.init();
                }).catch((error) => {
                    console.log(error.response.data);
                    this.error = error.response.data.message;
                })
            }
        },
        relocate(column_id, card_id, dir) {
            this.error = '';
            axios.post('/api/relocate-card',{
                'current_column_id': column_id,
                'card_id': card_id,
                'direction': dir
            })
            .then(() => {
                this.init();
            }).catch((error) => {
                console.log(error.response.data);
                this.error = error.response.data.message;
            })
        },
        editCard(id) {
            this.error = '';
            axios.get('/api/get-card-info/'+id)
            .then((response) => {
                this.edit_card_id = id;
                this.card_title = response.data.title;
                this.card_description = response.data.description;

                this.$modal.show('edit_card');
            }).catch((error) => {
                console.log(error.response.data);
                this.error = error.response.data.message;
            });            
        },
        closeModal() {
            this.edit_card_id = 0;
            this.card_title = '';
            this.card_description = '';

            this.$modal.hide('edit_card');
        },
        updateCard() {
            this.error = '';
            axios.post('/api/update-card',{
                'card_id': this.edit_card_id,
                'card_title': this.card_title,
                'card_description': this.card_description
            })
            .then(() => {
                this.init();
                this.closeModal();
            }).catch((error) => {
                console.log(error.response.data);
                this.closeModal();
                this.error = error.response.data.message;
            })
        },
        exportDB() {
            axios.get('/api/export-db', {responseType: 'arraybuffer'})
            .then((response) => {
                let blob = new Blob([response.data], { type: 'application/octet-stream' })
                let link = document.createElement('a')
                link.href = window.URL.createObjectURL(blob)
                link.download = 'dump.sql'
                link.click();
            }).catch((error) => {
                console.log(error.response.data);
            });   
        }
    }
}
</script>
