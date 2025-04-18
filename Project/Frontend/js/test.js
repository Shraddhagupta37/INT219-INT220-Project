class TestTimer {
    constructor(duration, displayElement, onTimeUp) {
        this.timeLeft = duration;
        this.display = displayElement;
        this.onTimeUp = onTimeUp;
        this.interval = null;
    }

    start() {
        this.interval = setInterval(() => {
            this.updateDisplay();
            if (this.timeLeft === 0) {
                this.stop();
                this.onTimeUp();
            }
            this.timeLeft--;
        }, 1000);
    }

    stop() {
        clearInterval(this.interval);
    }

    updateDisplay() {
        const minutes = Math.floor(this.timeLeft / 60);
        const seconds = this.timeLeft % 60;
        this.display.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
    }
}

// Test submission handling
document.getElementById('test-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const answers = {};
    const formData = new FormData(this);
    
    formData.forEach((value, key) => {
        if (key.startsWith('q')) {
            const questionId = key.substring(1);
            answers[questionId] = value;
        }
    });

    // Submit answers
    fetch('/Backend/PHP/submit_test.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            test_id: testId,
            answers: answers,
            time_taken: initialDuration - timeLeft
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = `/Frontend/Pages/test-result.php?attempt_id=${data.attempt_id}`;
        } else {
            alert('Failed to submit test. Please try again.');
        }
    });
}); 