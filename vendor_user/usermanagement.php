
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bootstrap demo</title>
    <script src="userManagement.js"></script>
  </head>
  <body>
    <h2 class="m-3">User Management</h2>

    <div class="m-3 row">
    <div class="w-25">
    <input type="text" class="form-control" id="searchTerm" placeholder="Search users">
    </div>
    <div class="col-auto">
    <button type="button" class="btn btn-success rounded-3" data-bs-toggle="modal" data-bs-target="#formModal" data-bs-whatever="">Create User</button>        
    </div>
    </div>

    <div class="m-3 border rounded-2" style="height: 70vh; overflow: auto;">
    <table id="userTable" class="table table-hover">
    <thead>
    <tr>
      <th scope="col">Id</th>
      <th scope="col">Fullname</th>
      <th scope="col">Username</th>
      <th scope="col">Password</th>
      <th scope="col">Profile</th>
      <th scope="col">Picture</th>
      <th scope="col">Actions</th>
    </tr>
    </thead>
    <tbody>
    </tbody>
    </table>
    </div>

    <div class="modal fade" id="formModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Create User</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="userForm" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="recipient-name" class="col-form-label">Fullname:</label>
                            <input type="text" id="fullname" class="form-control" name="fullname" required>
                        </div>
                        <div class="mb-3">
                            <label for="message-text" class="col-form-label">Username:</label>
                            <input type="text" id="username" class="form-control" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="message-text" class="col-form-label">Password:</label>
                            <input type="password" id="password" class="form-control" name="password" required>
                            <div id="passwordHelpBlock" class="form-text">Your password must be 8-20 characters long, contain letters and numbers, and must not contain spaces, special characters, or emoji.</div>
                        </div>
                        <div class="mb-3">
                            <label for="message-text" class="col-form-label">Profile:</label>
                            <select id="profile" class="form-select" aria-label="Default select example" name="profile" required>
                                <option selected>Choose Profile</option>
                                <option value="Administrator">Administrator</option>
                                <option value="Inspector">Inspector</option>
                                <option value="Vendor">Vendor</option>
                                <option value="Resident">Resident</option>
                                <option value="User">User</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="message-text" class="col-form-label">Profile Picture:</label>
                            <input type="file" id="picture" class="newPics" name="picture">
                            <div class="help-block" class="form-text">Maximum size 200Mb</div>
                            <img class="preview" src="person-square.svg" alt="Image Preview" style="width:100px;">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="submit" class="btn btn-success rounded-3">Create</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Update User</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editForm" enctype="multipart/form-data">
                        <div class="mb-3">
                            <input type="hidden" id="editId" name="editId">
                            <label for="recipient-name" class="col-form-label">Fullname:</label>
                            <input type="text" id="editFullname" class="form-control" name="editFullname" required>
                        </div>
                        <div class="mb-3">
                            <label for="message-text" class="col-form-label">Username:</label>
                            <input type="text" id="editUsername" class="form-control" name="editUsername" required>
                        </div>
                        <div class="mb-3">
                            <label for="message-text" class="col-form-label">Profile:</label>
                            <select id="editProfile" class="form-select" aria-label="Default select example" name="editProfile" required>
                                <option selected>Choose Profile</option>
                                <option value="Administrator">Administrator</option>
                                <option value="Inspector">Inspector</option>
                                <option value="Vendor">Vendor</option>
                                <option value="Resident">Resident</option>
                                <option value="User">User</option>
                            </select>
                        </div>

                        <div class="row">
                        <button class="btn btn-success rounded-3 col m-3" type="button" data-bs-toggle="collapse" data-bs-target="#updatePassword" aria-expanded="false" aria-controls="collapseExample">Update password</button>
                        <button class="btn btn-success rounded-3 col m-3" type="button" data-bs-toggle="collapse" data-bs-target="#updatePicture" aria-expanded="false" aria-controls="collapseExample">Update picture</button>
                        </div>

                        <div class="collapse mb-3" id="updatePassword">
                        <div class="card card-body">
                        <div class="mb-3">
                            <label for="message-text" class="col-form-label">New Password:</label>
                            <input type="password" id="editPassword" class="form-control" name="editPassword">
                            <div id="passwordHelpBlock" class="form-text">Your password must be 8-20 characters long, contain letters and numbers, and must not contain spaces, special characters, or emoji.</div>
                        </div>
                        </div>
                        </div>

                        <div class="collapse mb-3" id="updatePicture">
                        <div class="card card-body">
                        <div class="mb-3">
                            <label for="message-text" class="col-form-label">Profile Picture:</label>
                            <input type="file" id="editPicture" class="newPics" name="editPicture">
                            <div class="help-block" class="form-text">Maximum size 200Mb</div>
                            <img class="preview" src="person-square.svg" alt="Image Preview" style="width:100px;">
                        </div>
                        </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="submit" class="btn btn-success rounded-3">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
  </body>
</html>