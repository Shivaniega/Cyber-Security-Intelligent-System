<?php
header('Content-Type: application/json');

// === Fake suspicious websites commonly used in spam/phishing ===
$fakeWebsites = [
    "url_freeoffer_com", "url_recharge-now_com", "url_fastcash_net", "url_winner-prizes_org",
    "url_claimreward_com", "url_cheap-recharge_net", "url_getbonus_online", "url_prizewinner_co",
    "url_instantcash_rewards", "url_free-mobile-recharge_com", "url_unlimited-cash_net",
    "url_cashback-deals_org", "url_quick-recharge_net", "url_bonus-offer_com", "url_winprize_today",
    "url_indiafree-recharge_com", "url_mobilerecharge-claim_net", "url_cash-prize-org",
    "url_security-alert_com", "url_verify-account_net"
];

// === Expanded safe trusted websites ===
$safeWebsites = [
    "url_jio_com", "url_airtel_in", "url_vodafone_in", "url_idea_in", "url_paytm_com",
    "url_google_pay_com", "url_phonepe_com", "url_mobikwik_com", "url_freecharge_com",
    "url_jiostore_com", "url_mtnl_net_in",
    "url_apple_com", "url_amazon_com", "url_paypal_com", "url_visa_com", "url_mastercard_com",
    "url_samsung_com", "url_ebay_com", "url_netflix_com", "url_airbnb_com", "url_walmart_com",
    "url_aliexpress_com", "url_google_com", "url_microsoft_com", "url_facebook_com", "url_instagram_com",
    "url_whatsapp_com", "url_twitter_com", "url_linkedin_com", "url_uber_com", "url_spotify_com",
    "url_snapchat_com", "url_reddit_com", "url_discord_com", "url_zoom_us", "url_slack_com",
];

// === Common spam keywords ===
$spamKeywords = [
    "free", "win", "winner", "won", "cash", "prize", "bonus", "urgent", "offer",
    "limited", "click", "buy", "cheap", "money", "deal", "save", "discount", "exclusive",
    "credit", "loan", "earn", "income", "guaranteed", "now", "apply", "risk",
    "lowest", "trial", "access", "instant", "claim", "gift", "reward", "promotion",
    "special", "sale", "subscribe", "unsubscribe", "selected", "congratulations",
    "account", "suspended", "blocked", "update", "password", "verify", "otp",
    "pin", "login", "security", "alert", "support", "helpdesk", "contact",
    "clickhere", "link", "callnow", "tollfree", "winner", "prize", "claimnow"
];

// === Training data: [message, isSpam] ===
$trainingData = [
    ["Your Jio recharge of Rs. 199 was successful. Valid till 28-Oct-2025. Visit https://jio.com for details.", false],
    ["Airtel prepaid recharge of Rs. 150 done successfully. Check https://airtel.in for plans.", false],
    ["Vodafone recharge Rs. 199 successful. Manage at https://vodafone.in.", false],
    ["Your prepaid recharge is completed. Thanks for choosing us. More info at https://paytm.com.", false],
    ["Thank you for your recharge. Visit https://google_pay.com for offers.", false],
    ["Idea prepaid plan activated successfully. Validity 28 days.", false],
    ["Your plan has been renewed successfully. For balance, dial *123#.", false],
    ["Mobile recharge of Rs. 299 successful. Enjoy your calls and data.", false],
    ["Your telecom recharge is completed successfully. Visit https://freecharge.com.", false],
    ["Dear user, your Jio prepaid recharge is completed. Thank you for choosing Jio.", false],
    ["Amazon order confirmed. Visit https://amazon.com to track your shipment.", false],
    ["Your PayPal transaction of $150 was successful. Check details at https://paypal.com.", false],
    ["Apple device warranty activated. More info at https://apple.com.", false],
    ["ALERT! 100% daily data exhausted ", false],


    ["Congratulations! You won a free recharge. Claim at http://freeoffer.com now!", true],
    ["Get 100% cashback on recharge. Hurry! Visit http://recharge-now.com", true],
    ["Click here http://winner-prizes.org to claim your prize.", true],
    ["Urgent! Your account will be suspended unless you recharge at http://cheap-recharge.net", true],
    ["Exclusive offer! Instant cash at http://fastcash.net. Limited time only.", true],
    ["Buy cheap Jio recharges now, limited time offer! Visit http://getbonus.online", true],
    ["Free recharge for Airtel users. Click http://prizewinner.co to get now!", true],
    ["Your account is blocked. Verify now at http://security-alert.com", true],
    ["You have won a cash prize. Claim now at http://cash-prize-org", true],
    ["Urgent! Update your password immediately by visiting http://verify-account.net", true],
];
// Pattern matching for spam detection
    const spamPatterns = [
      {
        pattern: /\b(?:http?|ftp):\/\/[^\s/$.?#].[^\s]*\b/gi,
        description: "URL detected",
        weight: 4
      },
      {
        pattern: /\b\d{10,}\b/g,
        description: "Long number sequence",
        weight: 3
      },
      {
        pattern: /\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}\b/gi,
        description: "Email address",
        weight: 3
      },
      {
        pattern: /([!$%&])\1{2,}/g,
        description: "Repeated special characters",
        weight: 2
      },
      {
        pattern: /\b\d{3}[-.]?\d{3}[-.]?\d{4}\b/g,
        description: "Phone number pattern",
        weight: 3
      },
      {
        pattern: /[A-Z]{5,}/g,
        description: "Excessive capitalization",
        weight: 2
      },
      {
        pattern: /\b([A-Za-z])\1{3,}\b/g,
        description: "Repeated letters",
        weight: 2
      },
      {
        pattern: /\$\d+(?:\.\d{2})?(?:\s*(?:million|billion|thousand))?/gi,
        description: "Monetary amounts",
        weight: 3
      },
      {
        pattern: /(?:100|%)\s*(?:free|guarantee|satisfaction)/gi,
        description: "100% claims",
        weight: 3
      },
      {
        pattern: /(?:dear\s+(?:friend|customer|valued\s+client)|hello\s+here)/gi,
        description: "Impersonal greeting",
        weight: 2
      }
    ];
// Helper: sanitize and split text into lowercase words (simple tokenization)
function tokenize($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9_\-]+/', ' ', $text);
    $words = array_filter(explode(' ', $text));
    return $words;
}

// Count spam and ham occurrences of keywords for Naive Bayes
function trainNaiveBayes($trainingData, $spamKeywords) {
    $spamWordCounts = array_fill_keys($spamKeywords, 0);
    $hamWordCounts = array_fill_keys($spamKeywords, 0);
    $spamMessageCount = 0;
    $hamMessageCount = 0;

    foreach ($trainingData as list($message, $isSpam)) {
        $words = tokenize($message);
        if ($isSpam) {
            $spamMessageCount++;
            foreach ($words as $w) {
                if (isset($spamWordCounts[$w])) {
                    $spamWordCounts[$w]++;
                }
            }
        } else {
            $hamMessageCount++;
            foreach ($words as $w) {
                if (isset($hamWordCounts[$w])) {
                    $hamWordCounts[$w]++;
                }
            }
        }
    }

    return [
        'spamWordCounts' => $spamWordCounts,
        'hamWordCounts' => $hamWordCounts,
        'spamMessageCount' => $spamMessageCount,
        'hamMessageCount' => $hamMessageCount,
    ];
}

// Calculate naive Bayes probability that message is spam
function naiveBayesSpamProbability($message, $model) {
    $words = tokenize($message);

    $spamTotalWords = array_sum($model['spamWordCounts']) + 1;
    $hamTotalWords = array_sum($model['hamWordCounts']) + 1;

    // Laplace smoothing factor
    $alpha = 1;

    $logSpamProb = log($model['spamMessageCount'] / ($model['spamMessageCount'] + $model['hamMessageCount']));
    $logHamProb = log($model['hamMessageCount'] / ($model['spamMessageCount'] + $model['hamMessageCount']));

    foreach ($words as $word) {
        $spamWordCount = $model['spamWordCounts'][$word] ?? 0;
        $hamWordCount = $model['hamWordCounts'][$word] ?? 0;

        $pWordGivenSpam = ($spamWordCount + $alpha) / ($spamTotalWords + $alpha * count($model['spamWordCounts']));
        $pWordGivenHam = ($hamWordCount + $alpha) / ($hamTotalWords + $alpha * count($model['hamWordCounts']));

        $logSpamProb += log($pWordGivenSpam);
        $logHamProb += log($pWordGivenHam);
    }

    // Normalize probabilities (softmax)
    $maxLog = max($logSpamProb, $logHamProb);
    $spamProb = exp($logSpamProb - $maxLog);
    $hamProb = exp($logHamProb - $maxLog);

    return $spamProb / ($spamProb + $hamProb);
}

// Heuristic spam score based on keywords and suspicious URLs
function heuristicScore($message, $spamKeywords, $fakeWebsites, $safeWebsites, &$matchedKeywords, &$matchedFakeSites, &$matchedSafeSites) {
    $text = strtolower($message);
    $score = 0;
    $matchedKeywords = [];
    $matchedFakeSites = [];
    $matchedSafeSites = [];

    // Check spam keywords
    foreach ($spamKeywords as $keyword) {
        if (strpos($text, $keyword) !== false) {
            $score += 10;
            $matchedKeywords[] = $keyword;
        }
    }

    // Check fake websites
    foreach ($fakeWebsites as $fakeSite) {
        $siteStr = str_replace('_', '.', $fakeSite); // reverse your format to check URLs
        if (strpos($text, $siteStr) !== false) {
            $score += 20;
            $matchedFakeSites[] = $siteStr;
        }
    }

    // Check safe websites
    foreach ($safeWebsites as $safeSite) {
        $siteStr = str_replace('_', '.', $safeSite);
        if (strpos($text, $siteStr) !== false) {
            $score -= 15;
            $matchedSafeSites[] = $siteStr;
        }
    }

    // Clamp score to [0, 100]
    if ($score < 0) $score = 0;
    if ($score > 100) $score = 100;

    return $score;
}

// Main logic

// Get input message from POST
$message = $_POST['message'] ?? '';

if (!$message) {
    echo json_encode(['error' => 'No message provided']);
    exit;
}

// Train naive bayes model
$model = trainNaiveBayes($trainingData, $spamKeywords);

// Calculate probabilities
$nbSpamProb = naiveBayesSpamProbability($message, $model);

// Calculate heuristic score
$heuristic = heuristicScore($message, $spamKeywords, $fakeWebsites, $safeWebsites, $matchedKeywords, $matchedFakeSites, $matchedSafeSites);

// Final combined score (weighted average)
$finalScore = ($nbSpamProb * 70) + ($heuristic / 100 * 30);  // weights: 70% Naive Bayes + 30% heuristic

// Threshold to decide spam (e.g., 0.5)
$isSpam = $finalScore >= 0.5;

// Prepare response data with confidence % and matches
$response = [
    'isSpam' => $isSpam,
    'nb_confidence_pct' => round($nbSpamProb * 100, 2),
    'heuristic_score_pct' => round($heuristic, 2),
    'final_score_pct' => round($finalScore * 100, 2),
    'matches' => [
        'keywords' => $matchedKeywords,
        'fake_websites' => $matchedFakeSites,
        'safe_websites' => $matchedSafeSites
    ]
];

// Return JSON
echo json_encode($response);
