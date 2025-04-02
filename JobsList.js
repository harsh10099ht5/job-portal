import React, { useEffect, useState } from "react";
import axios from "axios";

const JobsList = () => {
  const [jobs, setJobs] = useState([]);

  useEffect(() => {
    axios.get("http://localhost:5000/api/jobs/all").then((res) => {
      setJobs(res.data);
    });
  }, []);

  return (
    <div>
      <h2>Job Listings</h2>
      {jobs.map((job) => (
        <div key={job._id}>
          <h3>{job.title} - {job.company}</h3>
          <p>{job.location}</p>
          <p>{job.description}</p>
        </div>
      ))}
    </div>
  );
};

export default JobsList;
