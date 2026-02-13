<!DOCTYPE html>
<html lang="en">
<head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Talind CSS</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
        <div class="flex items-center justify-center min-h-screen bg-gray-100">
    <div class="grid grid-cols-3 gap-4 p-6">

        <div class="p-6 font-bold text-center text-white bg-red-500 rounded-lg">
            Red
        </div>

        <div class="p-6 font-bold text-center text-white bg-blue-500 rounded-lg">
            Blue
        </div>

        <div class="p-6 font-bold text-center text-white bg-green-500 rounded-lg">
            Green
        </div>

        <div class="p-6 font-bold text-center text-black bg-yellow-500 rounded-lg">
            Yellow
        </div>

        <div class="p-6 font-bold text-center text-white bg-purple-500 rounded-lg">
            Purple
        </div>

        <div class="p-6 font-bold text-center text-white bg-pink-500 rounded-lg">
            Pink
        </div>

        <div class="flex items-center justify-center min-h-screen">
            <div class="grid grid-cols-3 gap-4">
                <div class="p-6 text-white bg-red-500">Red</div>
                <div class="p-6 text-white bg-blue-500">Blue</div>
                <div class="p-6 text-white bg-green-500">Green</div>
                <div class="p-6 text-black bg-yellow-500">Yellow</div>
                <div class="p-6 text-white bg-purple-500">Purple</div>
                <div class="p-6 text-white bg-pink-500">Pink</div>
            </div>
        </div>

    </div>
</div>


</body>
</html>