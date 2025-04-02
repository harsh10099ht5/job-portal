const express = require("express");
const Job = require("../models/Job");

const router = express.Router();

// Post a new job
router.post("/post", async (req, res) => {
  const job = new Job(req.body);
  await job.save();
  res.json({ message: "Job posted successfully!" });
});

// Get all jobs
router.get("/all", async (req, res) => {
  const jobs = await Job.find();
  res.json(jobs);
});

module.exports = router;
