<template>
  <div class="relative p-2 flex overflow-x-auto h-full">
    <!-- Columns (Statuses) -->

    <div
      v-for="status in statuses"
      :key="status.slug"
      @mouseover="showEditButton = true"
      @mouseleave="showEditButton = false"
      class="mr-6 w-4/5 max-w-xs flex-shrink-0"
    >
      <div class="rounded-md shadow-md overflow-hidden">
        <div class="p-3 flex justify-between items-baseline bg-blue-800">
          <h4 class="font-medium text-white">
            {{ status.title }}
          </h4>
          <button
            @click="openAddTaskForm(status.id)"
            class="py-1 px-2 text-sm text-orange-500 hover:underline"
          >
            Add Task
          </button>
        </div>
        <div class="p-2 bg-blue-100">
          <!-- AddTaskForm -->
          <AddTaskForm
            v-if="newTaskForStatus === status.id"
            :status-id="status.id"
            v-on:task-added="handleTaskAdded"
            v-on:task-canceled="closeAddTaskForm"
          />
          <EditTaskForm
            v-if="EditTaskForStatus === status.id"
            :task-id="task_ids"
            v-on:task-updated="handleTaskUpdated"
            v-on:task-canceled="closeAddTaskForm"
          />
          <!-- ./AddTaskForm -->

          <!-- Tasks -->
          <draggable
            class="flex-1 overflow-hidden"
            v-model="status.tasks"
            v-bind="taskDragOptions"
            @end="handleTaskMoved"
          >
            <transition-group
              class="flex-1 flex flex-col h-full overflow-x-hidden overflow-y-auto rounded shadow-xs"
              tag="div"
            >
              <div
                v-for="task in status.tasks"
                :key="task.id"
                class="mb-3 p-4 flex flex-col bg-white rounded-md shadow transform hover:shadow-md cursor-pointer"
              >
                <span class="block mb-2 text-xl text-gray-900">
                  {{ task.title }}
                </span>
                <p class="text-gray-700">
                  {{ task.description }}
                </p>

                <!-- Edit button -->
                <button
                  v-show="showEditButton"
                  class="text-sm text-orange-500 hover:underline absolute top-0 right-0 mr-2 mt-2 no-outline"
                  @click="editTask(task.status_id, task.id)"
                >
                  Edit
                </button>
                <button
                  v-show="showEditButton"
                  class="text-sm text-orange-500 hover:underline absolute top-0 right-0 mr-2 mt-12 no-outline"
                  @click="deleteTask(task.id)"
                >
                  Delete
                </button>
              </div>
            </transition-group>
          </draggable>
          <!-- No Tasks -->
          <div
            v-show="!status.tasks.length && newTaskForStatus !== status.id"
            class="flex-1 p-4 flex flex-col items-center justify-center"
          >
            <span class="text-gray-600">No tasks yet</span>
            <button
              class="mt-1 text-sm text-orange-600 hover:underline"
              @click="openAddTaskForm(status.id)"
            >
              Add one
            </button>
          </div>
          <!-- ./No Tasks -->
        </div>
      </div>
    </div>
    <!-- ./Columns -->

    <div>
    <!-- Button to open the modal -->
   
    <!-- Modal content -->
    <!-- <div
      class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
      @click="closeModal"
      v-if="showModal"
    > -->
      <form @submit.prevent="saveTask" class="w-64">
        <div class="bg-white rounded p-6">
          <!-- Input field -->
          <input
            type="text"
            class="w-full border rounded-md px-3 py-2"
            v-model=" board.title"
            placeholder="Enter board title"
          />
          <div v-show="errorMessage">
        <span class="text-xs text-red-500">
          {{ errorMessage }}
        </span>
      </div>
          <!-- Buttons for save and cancel -->
          <div class="mt-4 flex justify-end">
            <button
              type="submit"
              class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 focus:outline-none"
            >
              Save
            </button>
          
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import draggable from "vuedraggable";
import AddTaskForm from "./AddTaskForm";
import EditTaskForm from "./EditTaskForm";

export default {
  components: { draggable, AddTaskForm, EditTaskForm },
  props: {
    initialData: Array
  },
  data() {
    return {
      statuses: [],

      newTaskForStatus: 0,
      EditTaskForStatus: 0,
      task_ids: 0,
      showEditButton: false,
      board:{
        title: "",
      },
      
      errorMessage: ""
    
    };
  },
  computed: {
    taskDragOptions() {
      return {
        animation: 210,
        group: "task-list",
        dragClass: "status-drag"
      };
    }
  },
  mounted() {
    // 'clone' the statuses so we don't alter the prop when making changes
    // this.statuses = JSON.parse(JSON.stringify(this.initialData));
    this.get();
  },
  methods: {
  
    saveTask() {
      if (!this.board.title) {
        this.errorMessage = "The title field is required";
        return;
      }
      axios
        .post("/statuses", this.board)
        .then(res => {
          // Tell the parent component we've added a new task and include it
         this.get();
         this.board.title = '';
        })
        .catch(err => {
          // Handle the error returned from our request
          this.handleErrors(err);
        });
      
    },
    deleteTask(taskId) {
      axios
        .delete(`remove/${taskId}`)
        .then(res => {
          // Tell the parent component we've added a new task and include it
          // this.statuses = res.data;
          this.get();
        })
        .catch(err => {
          // Handle the error returned from our request
          this.handleErrors(err);
        });
    },
    editTask(status_id, task_id) {
      this.EditTaskForStatus = status_id;
      this.task_ids = task_id;
    },

    openAddTaskForm(statusId) {
      this.newTaskForStatus = statusId;
    },
    closeAddTaskForm() {
      this.newTaskForStatus = 0;
      this.EditTaskForStatus = 0;
    },

    handleTaskAdded(newTask) {
      // Find the index of the status where we should add the task
      const statusIndex = this.statuses.findIndex(
        status => status.id === newTask.status_id
      );

      // Add newly created task to our column
      this.statuses[statusIndex].tasks.push(newTask);

      // Reset and close the AddTaskForm
      this.closeAddTaskForm();
      this.get();
    },
    handleTaskUpdated(all_task) {
      const statusIndex = this.statuses.findIndex(
        status => status.id === all_task.status_id
      );

      // Check if the task already exists in the status
      const existingTaskIndex = this.statuses[statusIndex].tasks.findIndex(
        task => task.id === all_task.id
      );

      if (existingTaskIndex !== -1) {
        // If the task already exists, update it
        this.$set(
          this.statuses[statusIndex].tasks,
          existingTaskIndex,
          all_task
        );
      } else {
        // If the task is new, add it to the status
        this.statuses[statusIndex].tasks.push(all_task);
      }

      // Reset and close the AddTaskForm
      this.closeAddTaskForm();
      this.get();
    },
    handleTaskMoved(evt) {
      axios
        .put("/tasks/sync", { columns: this.statuses })
        .then(response => {
          this.get();
        })
        .catch(err => {
          console.log(err.response);
        });
    },

    get(evt) {
      axios
        .get("/all-tasks", this.newTask)
        .then(res => {
          // Tell the parent component we've added a new task and include it
          this.statuses = res.data;
        })
        .catch(err => {
          // Handle the error returned from our request
          this.handleErrors(err);
        });
    }
  }
};
</script>

<style scoped>
.status-drag {
  transition: transform 0.5s;
  transition-property: all;
}
.no-outline:focus {
  outline: none;
}
</style>
