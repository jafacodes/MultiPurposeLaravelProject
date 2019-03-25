<template>
  <div class="container">
    <div class="row mt-3" v-if="$gate.isAdmin()">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Users Table</h3>

            <div class="card-tools">
              <button class="btn btn-success" @click="newModal">
                Add New User
                <i class="fas fa-user-plus blue fa-fw"></i>
              </button>
            </div>
          </div>
          <!-- /.card-header -->
          <div class="card-body table-responsive p-0">
            <table class="table table-hover">
              <tbody>
                <tr>
                  <th>ID</th>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Type</th>
                  <th>Registered at</th>
                  <th>Registered From</th>
                  <th v-show="currentUser.type == 'admin'">Modify</th>
                </tr>
                <tr v-for="user in users" v-bind:key="user.id">
                  <td>{{ user.id }}</td>
                  <td>{{ user.name }}</td>
                  <td>{{ user.email }}</td>
                  <td>{{ user.type | upText }}</td>
                  <td>{{ user.created_at | dateForm }}</td>
                  <td>{{ user.created_at | fromNowDate }}</td>
                  <td v-show="currentUser.id != user.id && currentUser.type == 'admin'">
                    <a href="#" @click="editModal(user)">
                      <i class="fas fa-pen-square blue"></i>
                    </a>
                    /
                    <a  href="#" @click="deleteUser(user)">
                      <i class="fas fa-trash-alt red"></i>
                    </a>
                  </td>
                  <td v-show="currentUser.id == user.id && currentUser.type == 'admin'"></td>
                </tr>
              </tbody> 
            </table>
          </div>
          <!-- /.card-body -->
        </div>
        <!-- /.card -->
      </div>
    </div>

    <div class="row mt-3" v-if="!$gate.isAdmin()">
      <div class="col-md-10">
        <not-found></not-found>
      </div>
    </div>
    <!-- Modal -->
    <div
      class="modal fade"
      id="addNew"
      tabindex="-1"
      role="dialog"
      aria-labelledby="addNew"
      aria-hidden="true"
    >
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 v-show="!editMode" class="modal-title" id="addNew">Add New User</h5>
            <h5 v-show="editMode" class="modal-title" id="addNew">Update User's Info</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <form @submit.prevent="editMode ? updateUser() : createUser()">
            <div class="modal-body">
              <div class="form-group">
                <input
                  placeholder="Enter Name ..."
                  v-model="form.name"
                  type="text"
                  name="name"
                  class="form-control"
                  :class="{ 'is-invalid': form.errors.has('name') }"
                >
                <has-error :form="form" field="name"></has-error>
              </div>
              <div class="form-group">
                <input
                  placeholder="Enter Email ..."
                  v-model="form.email"
                  type="email"
                  name="email"
                  class="form-control"
                  :class="{ 'is-invalid': form.errors.has('email') }"
                >
                <has-error :form="form" field="email"></has-error>
              </div>
              <div class="form-group">
                <textarea
                  v-model="form.bio"
                  name="bio"
                  id="bio"
                  placeholder="Short bio for user (Optional)"
                  class="form-control"
                  :class="{ 'is-invalid': form.errors.has('bio') }"
                ></textarea>
                <has-error :form="form" field="bio"></has-error>
              </div>

              <div class="form-group">
                <select
                  name="type"
                  v-model="form.type"
                  id="type"
                  class="form-control"
                  :class="{ 'is-invalid': form.errors.has('type') }"
                >
                  <option value>Select User Role</option>
                  <option value="admin">Admin</option>
                  <option value="user">Standard User</option>
                  <option value="author">Author</option>
                </select>
                <has-error :form="form" field="type"></has-error>
              </div>
              <div class="form-group">
                <input
                  placeholder="Enter Password ..."
                  v-model="form.password"
                  type="password"
                  name="password"
                  class="form-control"
                  :class="{ 'is-invalid': form.errors.has('password') }"
                >
                <has-error :form="form" field="password"></has-error>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-danger" data-dismiss="modal">
                Close
                <i class="fas fa-times-circle fa-fw"></i>
              </button>
              <button v-show="!editMode" type="submit" class="btn btn-primary">
                Create
                <i class="fas fa-plus-square fa-fw"></i>
              </button>
              <button v-show="editMode" type="submit" class="btn btn-success">
                Update
                <i class="fas fa-edit fa-fw"></i>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      // Create a new form instance
      editMode: false,
      users: {},
      form: new Form({
        id: "",
        name: "",
        email: "",
        password: "",
        type: "",
        bio: "",
        photo: ""
      }),
      currentUser: {}
    };
  },
  methods: {
    newModal() {
      this.editMode = false;
      this.form.reset();
      $("#addNew").modal("show");
    },
    editModal(user) {
      this.editMode = true;
      this.form.reset();
      $("#addNew").modal("show");
      this.form.fill(user);
    },
    getCurrentUser() {
      axios
        .get("api/user/show")
        .then(({ data }) => (this.currentUser = data));
    },
    loadUsers() {
      if(this.$gate.isAdmin()){
        axios.get("api/user").then(({ data }) => (this.users = data.data));
      }
    },
    createUser() {
      this.$Progress.start();
      this.form
        .post("api/user")
        .then(() => {
          Toast.fire({
            type: "success",
            title: "User created successfully"
          });
          this.loadUsers();
          $("#addNew").modal("hide");
          this.$Progress.finish();
        })
        .catch(() => {
          Toast.fire({
            type: "error",
            title: "Failed to create new user !"
          });
          this.$Progress.fail();
        });
    },
    updateUser() {
      this.$Progress.start();
      this.form
        .put("api/user/" + this.form.id)
        .then(() => {
          Toast.fire({
            type: "success",
            title: "User updated successfully"
          });
          this.loadUsers();
          $("#addNew").modal("hide");
          this.$Progress.finish();
        })
        .catch(() => {
          Toast.fire({
            type: "error",
            title: "Failed to update the user !"
          });
          this.$Progress.fail();
        });
    },
    deleteUser(user) {
      Swal.fire({
        title: "Are you sure, you want to delete " + user.name + " ?",
        text: "You won't be able to revert this!",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!"
      }).then(result => {
        if (result.value) {
          this.form
            .delete("api/user/" + user.id)
            .then(() => {
              this.loadUsers();
              Swal.fire(
                "User Deleted!",
                "User " + user.name + " has been deleted.",
                "success"
              );
            })
            .catch(() => {
              Swal.fire(
                "Failed to delete user: " + user.name,
                "User " + user.name + " still exist!",
                "error"
              );
            });
        }
      });
    }
  },
  mounted() {
    this.getCurrentUser();
    this.loadUsers();
    console.log("Component mounted.");
  }
};
</script>
