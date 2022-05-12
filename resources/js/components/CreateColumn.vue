<template>
    <div class="d-flex">
        <section class="section">
            <h2>Add new Column</h2>
            <div class="container">
                <input type="text" v-model="columnName" placeholder="Column name" />
                <button v-on:click="addColumn()" class="button is-black">Add</button>

                <div class="error">{{error}}</div>
            </div>
        </section>

        <section class="section" v-if="columns.length > 0">
            <h2>Add new Card</h2>
            <div class="container">
                <div style="display: inline-block; vertical-align: top;">
                    <input type="text" v-model="cardName" placeholder="Card name" style="width: 250px; margin-bottom: 10px;" /><br/>
                    <select v-model="columnId" style="width: 280px;">
                        <option value="">-- Select Column --</option>
                        <option :value="col.id" v-for="(col, key) in columns" v-bind:key="key">{{ col.name }}</option>
                    </select>
                </div>
                <textarea v-model="description" cols="50" rows="5" placeholder="Description .."></textarea>
                <button v-on:click="addCard()" class="button is-black">Add</button>
                
                <div class="error">{{error_card}}</div>
            </div>
        </section>
    </div>
</template>

<script>
export default {
    props: ['columns'],
    data() {
        return {
            columnName: '',
            columnId: '',
            cardName: '',
            description: '',
            error: '',
            error_card: '',
        }
    },
    methods: {
        addColumn() {
            this.error = ''; //reset

            axios.post('/api/create-column', {
                'column_name': this.columnName
            }).then(() => {
                this.columnName = '';
                this.$emit('load-board');
            }).catch((error) => {
                this.error = error.response.data.message;    
            })
        },
        addCard() {
            this.error_card = ''; //reset

            axios.post('/api/create-card', {
                'column_id': this.columnId,
                'card_name': this.cardName,
                'description': this.description
            }).then(() => {
                this.columnId = '';
                this.cardName = '';
                this.description = '';
                this.$emit('load-board');
            }).catch((error) => {
                this.error_card = error.response.data.message;    
            })
        }
    }
}
</script>
