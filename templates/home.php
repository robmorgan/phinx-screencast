<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phinx Guestbook</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">
    <div class="container mx-auto px-4 py-8 max-w-2xl">
        <h1 class="text-4xl font-bold mb-8 text-center text-blue-600">Phinx Guestbook</h1>

        <?php if (isset($flashMessage)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?= htmlspecialchars($flashMessage) ?></span>
            </div>
        <?php endif; ?>
        
        <div class="bg-white shadow-md rounded-lg p-6 mb-8">
            <h2 class="text-2xl font-semibold mb-4 text-gray-800">Messages</h2>
            <?php foreach ($messages as $message): ?>
                <div class="border-b border-gray-200 py-4 last:border-b-0">
                    <div class="flex justify-between items-baseline">
                        <strong class="text-lg text-blue-500"><?= htmlspecialchars($message['name']) ?></strong>
                        <small class="text-gray-500"><?= $message['created_at'] ?></small>
                    </div>
                    <p class="mt-2 text-gray-700"><?= htmlspecialchars($message['message']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-2xl font-semibold mb-4 text-gray-800">Sign the Guestbook</h2>
            <form action="/sign" method="post" class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Your Name</label>
                    <input type="text" id="name" name="name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                </div>
                <div>
                    <label for="message" class="block text-sm font-medium text-gray-700">Your Message</label>
                    <textarea id="message" name="message" rows="4" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"></textarea>
                </div>
                <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-150 ease-in-out">
                    Submit
                </button>
            </form>
        </div>
    </div>
</body>
</html>