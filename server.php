<?php

// Include the Composer autoloader
require_once 'vendor/autoload.php';
use SimpleHtmlDom\HtmlWeb;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Read input data
$inputData = json_decode(file_get_contents('php://input'), true);

// Initialize the HTML DOM Parser
$htmlWeb = new HtmlWeb();

// Initialize the ChatGPT API
$apiKey = "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"; // Replace this with your actual API key
$chatGptUrl = "https://api.openai.com/v1/engines/davinci-codex/completions";

// Initialize the Spreadsheet
$spreadsheet = new Spreadsheet();
$worksheet = $spreadsheet->getActiveSheet();
$row = 1;

// Loop through the input data and process each item
foreach ($inputData as $item) {
    // Scrape website content
    $html = $htmlWeb->load($item['website_url']);
    $scrapedContent = scrapeContent($html); // Call the scrapeContent function to extract the required content

    // Generate email using ChatGPT API
    $email = generateEmail($apiKey, $chatGptUrl, $item, $scrapedContent);

    // Write the generated email to the Excel file
    $worksheet->setCellValue("A$row", $item['company_name']);
    $worksheet->setCellValue("B$row", $email);
    $row++;
}

// Save the Excel file
$writer = new Xlsx($spreadsheet);
$filename = 'generated_emails.xlsx';
$writer->save($filename);

// Return the download link
echo json_encode(['download_link' => $filename]);

// Function to scrape content from the HTML
function scrapeContent($html) {
    // Extract the required content from the HTML as per your requirements
    // For example, let's extract the text from all the paragraph tags
    $scrapedContent = "";
    foreach ($html->find('p') as $paragraph) {
        $scrapedContent .= $paragraph->plaintext . "\n";
    }
    return $scrapedContent;
}

// Function to generate email using ChatGPT API
function generateEmail($apiKey, $chatGptUrl, $item, $scrapedContent) {
    // Compose the prompt for the ChatGPT API
    $prompt = "Generate an email for the company {$item['company_name']} using the following information:\n";
    $prompt .= "Website URL: {$item['website_url']}\n";
    $prompt .= "Scraped content: $scrapedContent\n";
    // Add other user-provided information as required

    // Call the ChatGPT API
    $ch = curl_init($chatGptUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'prompt' => $prompt,
        'max_tokens' => 150,
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Bearer $apiKey",
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    // Extract the generated email from the API response
    $response = json_decode($response, true);
    $email = $response['choices'][0]['text'];

    return $email;
}

?>
