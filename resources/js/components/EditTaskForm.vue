<template>
  <form
    class="relative mb-3 flex flex-col justify-between bg-white rounded-md shadow overflow-hidden"
    @submit.prevent="handleUpdateTask"
  >
    <div class="p-3 flex-1">
      <input
        class="block w-full px-2 py-1 text-lg border-b border-blue-800 rounded no-outline"
        type="text"
        placeholder="Enter a titles"
        v-model.trim="all_task.title"
      />
      <!-- <textarea
        class="mt-3 p-2 block w-full p-1 border text-sm rounded"
        rows="2"
        placeholder="Add a description (optional)"
        v-model.trim="all_task.description"
      ></textarea> -->
      <div v-show="errorMessage">
        <span class="text-xs text-red-500">
          {{ errorMessage }}
        </span>
      </div>
    </div>
    <div class="p-3 flex justify-between items-end text-sm bg-gray-100">
      <button
        @click="$emit('task-canceled')"
        type="reset"
        class="py-1 leading-5 text-gray-600 hover:text-gray-700"
      >
        Cancel
      </button>
      <button
        type="update"
        class="px-3 py-1 leading-5 text-white bg-orange-600 hover:bg-orange-500 rounded"
      >
        update
      </button>
    </div>
  </form>
</template>

<script>
export default {
  props: {
    statusId: Number,
    taskId: Number
  },
  data() {
    return {
      all_task: {
        title: "",
        description: ""
      },
      all_task: [],
      errorMessage: ""
    };
  },
  mounted() {
    this.handle();
  },
  methods: {
    handleUpdateTask() {
      axios
        .put(`task-update/${this.taskId}`, this.all_task)
        .then(res => {
          // Tell the parent component we've added a new task and include it
          this.$emit("task-updated", res.data);
        })
        .catch(err => {
          // Handle the error returned from our request
          this.handleErrors(err);
        });
    },
    handle() {
      // Send new task to server
      axios
        .get(`id-wise-task/${this.taskId}`)
        .then(res => {
          this.all_task = res.data;
        })
        .catch(err => {
          // Handle the error returned from our request
          this.handleErrors(err);
        });
    },
    handleErrors(err) {
      if (err.response && err.response.status === 422) {
        // We have a validation error
        const errorBag = err.response.data.errors;
        if (errorBag.title) {
          this.errorMessage = errorBag.title[0];
        } else if (errorBag.description) {
          this.errorMessage = errorBag.description[0];
        } else {
          this.errorMessage = err.response.message;
        }
      } else {
        // We have bigger problems
        console.log(err.response);
      }
    }
  }
};
</script>

