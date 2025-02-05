<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Market Approval</title>
</head>
<body>
<div class="container mb-3 shadow p-3">
        <h1>Businesses</h1>
        <div class="col-sm-4 col-md-3 col-lg-3">
            <input class="form-control my-3 rounded-3" type="text" id="searchMarket" placeholder="Search Market">
        </div>
        <div class="m-3 border rounded-2" style="height: 70vh; overflow: auto;">
        <table id="marketsTable" class="table table-hover">
        <thead>
        <tr class="sticky-top">
        <th scope="col">Id</th>
        <th scope="col">Name</th>
        <th scope="col">Description</th>
        <th scope="col">Action</th>
        </tr>
        </thead>
        <tbody>
        </tbody>
        </table>
        </div>
    </div>
</body>
</html>