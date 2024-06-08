<template>
  <div class="relative p-2 flex overflow-x-auto h-full">
    <draggable
      class="flex dragArea"
      axis="x"
      v-model="statuses"
      @end="handleStatusMoved"
    >
      <div
        v-for="status in statuses"
        :key="status.slug"
        @mouseover="showEditButton = true"
        @mouseleave="showEditButton = false"
        class="mr-6 w-4/5 max-w-xs flex-shrink-0"
      >
        <div class="rounded-md shadow-md overflow-hidden">
          <div class="p-3 flex justify-between items-baseline bg-blue-800">
            <!-- <h4 class="font-medium text-white">
              {{ status.title }}
            </h4> -->
            <div class="flex items-baseline">
              <h4
                v-if="!isEditing(status.id)"
                class="font-medium text-white cursor-pointer"
                @click="startEditing(status.id)"
              >
                {{ status.title }}
              </h4>
              <input
                v-if="isEditing(status.id)"
                v-model="editingTitle"
                :ref="'input_' + status.id"
                class="font-medium text-white bg-blue-800"
                @blur="saveTitle(status.id)"
                @keyup.enter="saveTitle(status.id)"
              />
            </div>
            <button
              @click="archive(status.id)"
              class="py-1 px-2 text-sm text-orange-500 hover:underline"
            >
              Archive
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
                    class="text-sm text-orange-500 hover:underline absolute top-0 right-0 mr-2 mt-10 no-outline"
                    @click="deleteTask(task.id)"
                  >
                    Delete
                  </button>
                  <!-- <button
                    v-show="showEditButton"
                    class="text-sm text-orange-500 hover:underline absolute top-0 right-0 mr-2 mt-6 no-outline"
                    @click="deleteTask2(task.id)"
                  >
                    modal
                  </button> -->
                  <!-- <button
                    @click="isOpen = true"
                    @click="deleteTask2(task.id)"
                    class="text-sm text-orange-500 hover:underline absolute top-0 right-0 mr-2 mt-6 no-outline"
                  >
                    Modal
                  </button> -->
                </div>
              </transition-group>
            </draggable>
            <!-- No Tasks -->
            <!-- <div
              v-show="!status.tasks.length && newTaskForStatus !== status.id"
              class="flex-1 p-4 flex flex-col items-center justify-center"
            > -->
            <div class="flex-1 p-4 flex flex-col items-center justify-center">
              <!-- <span class="text-gray-600">No tasks yet</span> -->
              <button
                class="mt-1 mr-4 text-sm text-orange-600 hover:underline"
                @click="openAddTaskForm(status.id)"
              >
                Add Task
              </button>
            </div>
            <!-- ./No Tasks -->
          </div>
        </div>
      </div>
      <div>
        <div>
          <!-- Button to open the modal -->
          <!-- <button
            @click="isOpen = true"
            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
          >
            Open Modal
          </button> -->

          <!-- Modal -->
          <div
            v-if="isOpen"
            class="fixed z-10 inset-0 overflow-y-auto flex items-center justify-center"
          >
            <div class="modal-overlay fixed inset-0 bg-black opacity-25"></div>

            <!-- Modal Content -->
            <div
              class="modal-container bg-white w-64 mx-auto rounded shadow-lg z-50 overflow-y-auto"
            >
              <!-- Modal Header -->
              <div
                class="modal-header px-4 py-2 bg-gray-200 flex justify-between items-center"
              >
                <h3 class="text-lg font-semibold">Modal Title</h3>
                <button
                  @click="isOpen = false"
                  class="text-gray-600 hover:text-gray-800 focus:outline-none"
                >
                  <svg
                    class="h-6 w-6 fill-current"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                  >
                    <path
                      d="M5.293 6.707a1 1 0 011.414 0L12 10.586l5.293-5.293a1 1 0 111.414 1.414L13.414 12l5.293 5.293a1 1 0 01-1.414 1.414L12 13.414l-5.293 5.293a1 1 0 01-1.414-1.414L10.586 12 5.293 6.707z"
                    />
                  </svg>
                </button>
              </div>

              <!-- Modal Body -->
              <div class="modal-body p-4">
                <form @submit.prevent="saveTask2" class="w-64">
                  <div class="bg-white rounded p-6">
                    <input
                      type="text"
                      class="w-full border rounded-md px-3 py-2 no-outline"
                      v-model="boards.title"
                      placeholder="Enter board title"
                    />
                    <div v-show="errorMessage">
                      <span class="text-xs text-red-500">
                        {{ errorMessage }}
                      </span>
                    </div>
                    <button
                      type="submit"
                      class="px-3 py-1 leading-5 text-white bg-orange-600 hover:bg-orange-500 rounded"
                    >
                      Add
                    </button>
                  </div>
                </form>
              </div>

              <!-- Modal Footer -->
              <div class="modal-footer px-4 py-2 bg-gray-100 flex justify-end">
                <button
                  @click="isOpen = false"
                  class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                >
                  Close
                </button>
              </div>
            </div>
          </div>
        </div>
        <form @submit.prevent="saveTask" class="w-64">
          <div class="bg-white rounded p-6">
            <input
              type="text"
              class="w-full border rounded-md px-3 py-2 no-outline"
              v-model="board.title"
              placeholder="Enter board title"
            />
            <div v-show="errorMessage">
              <span class="text-xs text-red-500">
                {{ errorMessage }}
              </span>
            </div>
          </div>
        </form>
      </div>
    </draggable>
  </div>
</template>

<script>
import draggable from "vuedraggable";
import AddTaskForm from "./AddTaskForm";
import EditTaskForm from "./EditTaskForm";
import { useToast } from "vue-toastification";

export default {
  components: { draggable, AddTaskForm, EditTaskForm },
  props: {
    initialData: Array
  },
  data() {
    return {
      statuses: [],
      items: [],
      boards: [],
      newTaskForStatus: 0,
      EditTaskForStatus: 0,
      task_ids: 0,
      showEditButton: false,
      isOpen: false,
      board: {
        title: ""
      },
      errorMessage: "",
      editingStatusId: null,
      editingTitle: ""
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
    this.get();
  },
  methods: {
    // Task(){
    //    console.log(this.items);
    // },
    // saveTask2() {
    //   console.log(this.board);
    // },
    // deleteTask2(taskId) {
    //   this.isOpen = true;
    //   axios
    //     .get(`id-wise-task/${taskId}`)
    //     .then(res => {
    //       this.boards = res.data;
    //     })
    //     .catch(err => {
    //       // Handle the error returned from our request
    //       this.handleErrors(err);
    //     });
    // },
    startEditing(statusId) {
      // this.editingStatusId = statusId;
      // const status = this.statuses.find(status => status.id === statusId);
      // this.editingTitle = status.title;
      this.editingStatusId = statusId;
      const status = this.statuses.find(status => status.id === statusId);
      this.editingTitle = status.title;
      this.$nextTick(() => {
        const inputRef = this.$refs[`input_${statusId}`];
        if (inputRef && inputRef[0]) {
          inputRef[0].focus();
        }
      });
    },
    saveTitle(statusId) {
      axios
        .put(`/title-update/${statusId}`, { title: this.editingTitle })
        .then(response => {
          const status = this.statuses.find(status => status.id === statusId);
          status.title = this.editingTitle;
          this.editingStatusId = null;
          this.get();
        })
        .catch(err => {
          console.log(err.response);
        });
    },
    isEditing(statusId) {
      return this.editingStatusId === statusId;
    },
    handleStatusMoved() {
      console.log(this.statuses);

      axios
        .put("/status/sync", { columns: this.statuses })
        .then(response => {
          console.log(response);
        })
        .catch(err => {
          console.log(err.response);
        });
    },
    archive(status_id) {
      axios
        .put(`status-update/${status_id}`)
        .then(res => {
          this.$toast({
            render: h =>
              h("div", [
                h("span", "Status Archive  successfully."),
                h(
                  "button",
                  {
                    style: {
                      marginLeft: "10px",
                      color: "white",
                      backgroundColor: "green",
                      border: "none",
                      padding: "5px 10px",
                      cursor: "pointer"
                    },
                    on: {
                      click: () => {
                        this.undoUpdate(status_id);
                        this.$toast.clear();
                      }
                    }
                  },
                  "Undo"
                )
              ]),
            timeout: 5000,
            closeOnClick: true,
            onClose: () => {
              console.log("Toast closed");
            }
          });

          this.get();
        })
        .catch(err => {
          this.handleErrors(err);
        });
    },
    undoUpdate(status_id) {
      axios
        .put(`status-undo/${status_id}`)
        .then(res => {
          this.$toast.success("Undo updated successfully", {
            timeout: 5000,
            closeOnClick: true,
            onClose: () => {
              console.log("Toast closed");
            }
          });

          this.get();
        })
        .catch(err => {
          this.handleErrors(err);
        });
    },

    saveTask() {
      if (!this.board.title) {
        this.errorMessage = "The title field is required";
        return;
      }
      axios
        .post("/statuses", this.board)
        .then(res => {
          this.get();
          this.board.title = "";
        })
        .catch(err => {
          this.handleErrors(err);
        });
    },
    deleteTask(taskId) {
      axios
        .delete(`remove/${taskId}`)
        .then(res => {
          this.get();
        })
        .catch(err => {
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
      // this.closeAddTaskForm();
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
          this.closeAddTaskForm();
        })
        .catch(err => {
          console.log(err.response);
        });
    },

    get(evt) {
      axios
        .get("/all-tasks", this.newTask)
        .then(res => {
          this.statuses = res.data;
          this.items = res.data;
        })
        .catch(err => {
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
